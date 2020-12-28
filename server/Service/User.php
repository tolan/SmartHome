<?php

namespace SmartHome\Service;

use SmartHome\Common\Service;
use SmartHome\Entity\{
    User as UserEntity,
    Group as GroupEntity,
    Permission as PermissionEntity
};
use SmartHome\Common\Utils\Password;
use SmartHome\Database\EntityQuery;
use SmartHome\Cache;
use SmartHome\Enum;
use SlimSession;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class User {

    /**
     *
     * @var Service
     */
    private $_service;

    /**
     *
     * @var Cache\Storage;
     */
    private $_cache;

    /**
     * @var SlimSession\Helper
     */
    private $_session;

    /**
     * @Inject({"session", "cache"})
     */
    public function __construct ($session, $cache, Service $service) {
        /* @var $cache Cache\Factory */
        $this->_service = $service;
        $this->_cache = $cache->getCache(Enum\Cache::SCOPE_USER);
        $this->_session = $session;
    }

    /**
     *
     * @return array|null {user: UserEntity, groups: GroupEntity[], permissions: PermissionEntity[]}
     */
    public function getCurrentUser (): ?array {
        return $this->_session->get('user');
    }

    public function login (string $username, string $password): ?UserEntity {
        $query = EntityQuery::create(UserEntity::class, [[GroupEntity::class, PermissionEntity::class]], ['username' => $username]);
        $user = $this->_service->findOne($query); /* @var $user UserEntity */

        if (Password::verify($password, $user->getPassword())) {
            $token = uniqid('user_token_');
            $user->setToken($token);
            $this->_storeUser($user);

            $this->_cache->set($token, $user->getId(), Enum\Cache::TTL_7_DAYS);
            return $user;
        } else {
            return null;
        }
    }

    public function logout (string $username): bool {
        if (($user = $this->_session->get('user')) && $user['user']->getUsername() === $username) {
            $this->_cache->delete($user['user']->getToken());
            $this->_session->delete('user');
            return true;
        }

        return false;
    }

    public function refreshLogin (string $token): bool {
        $userId = $this->_cache->get($token);
        if ($userId) {
            $query = EntityQuery::create(UserEntity::class, [[GroupEntity::class, PermissionEntity::class]], ['id' => $userId]);
            $user = $this->_service->findOne($query); /* @var $user UserEntity */
            $user->setToken($token);
            $this->_storeUser($user);
            $this->_cache->set($token, $user->getId(), Enum\Cache::TTL_7_DAYS);
        }

        return false;
    }

    public function generateApiToken (int $userId): ?UserEntity {
        $query = EntityQuery::create(UserEntity::class, [], ['id' => $userId]);
        $user = $this->_service->findOne($query); /* @var $user User */

        if ($user) {
            $user->setApiToken(substr(md5(mt_rand()), 0, 8));
            $this->_service->persist($user);
        }

        return $user;
    }

    private function _storeUser (UserEntity $user): User {
        $permissions = [];
        foreach ($user->getGroups()->toArray() as $group) {
            foreach ($group->getPermissions()->toArray() as $permission) {
                $permissions[$permission->getId()] = $permission;
            }
        }

        $data = [
            'user' => $user,
            'groups' => $user->getGroups()->toArray(),
            'permissions' => array_values($permissions),
        ];
        $this->_session->set('user', $data);

        return $this;
    }

}
