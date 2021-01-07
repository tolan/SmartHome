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
use DI\Container;

/**
 * This file defines class for User service.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class User {

    /**
     * Common service
     *
     * @var Service
     */
    private $_service;

    /**
     * Cache
     *
     * @var Cache\Storage;
     */
    private $_cache;

    /**
     * Session
     *
     * @var SlimSession\Helper
     */
    private $_session;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_service = $container->get(Service::class);
        $this->_cache   = $container->get('cache')->getCache(Enum\Cache::SCOPE_USER);
        $this->_session = $container->get('session');
    }

    /**
     * Gets current logged user
     *
     * @return array|null {user: UserEntity, groups: GroupEntity[], permissions: PermissionEntity[]}
     */
    public function getCurrentUser(): ?array {
        return $this->_session->get('user');
    }

    /**
     * Login user
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return UserEntity|null
     */
    public function login(string $username, string $password): ?UserEntity {
        $query = EntityQuery::create(UserEntity::class, [[GroupEntity::class, PermissionEntity::class]], ['username' => $username]);
        $user  = $this->_service->findOne($query); /* @var $user UserEntity */

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

    /**
     * Logour user
     *
     * @param string $username Username
     *
     * @return bool
     */
    public function logout(string $username): bool {
        $user = $this->_session->get('user');
        if ($user && $user['user']->getUsername() === $username) {
            $this->_cache->delete($user['user']->getToken());
            $this->_session->delete('user');
            return true;
        }

        return false;
    }

    /**
     * Refresh login by given token
     *
     * @param string $token Token
     *
     * @return bool
     */
    public function refreshLogin(string $token): bool {
        $userId = $this->_cache->get($token);
        if ($userId) {
            $query = EntityQuery::create(UserEntity::class, [[GroupEntity::class, PermissionEntity::class]], ['id' => $userId]);
            $user  = $this->_service->findOne($query); /* @var $user UserEntity */
            $user->setToken($token);
            $this->_storeUser($user);
            $this->_cache->set($token, $user->getId(), Enum\Cache::TTL_7_DAYS);
        }

        return true;
    }

    /**
     * Generate API token
     *
     * @param int $userId User ID
     *
     * @return UserEntity|null
     */
    public function generateApiToken(int $userId): ?UserEntity {
        $query = EntityQuery::create(UserEntity::class, [], ['id' => $userId]);
        $user  = $this->_service->findOne($query); /* @var $user User */

        if ($user) {
            $user->setApiToken(substr(md5(mt_rand()), 0, 8));
            $this->_service->persist($user);
        }

        return $user;
    }

    /**
     * Saves user to session
     *
     * @param UserEntity $user User
     *
     * @return $this
     */
    private function _storeUser(UserEntity $user): User {
        $permissions = [];
        foreach ($user->getGroups()->toArray() as $group) {
            foreach ($group->getPermissions()->toArray() as $permission) {
                $permissions[$permission->getId()] = $permission;
            }
        }

        $data = [
            'user'        => $user,
            'groups'      => $user->getGroups()->toArray(),
            'permissions' => array_values($permissions),
        ];
        $this->_session->set('user', $data);

        return $this;
    }

}
