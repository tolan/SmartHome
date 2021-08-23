<?php

namespace SmartHome\Rest\User;

use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use SmartHome\Entity\{
    User,
    Group
};
use SmartHome\Common\Utils\Password;
use SmartHome\Enum\{
    HttpStatusCode,
    Notification as NotificationType,
    Permission
};
use SmartHome\Common\Notification;
use SmartHome\Authorization\Authorize;
use SmartHome\Database\EntityQuery;
use SmartHome\Common\Service;
use SmartHome\Service\User as UserService;

/**
 * This file defines class for User controller
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     * Authorization instance
     *
     * @var Authorize
     */
    private $_authorize;

    /**
     * Common service instance
     *
     * @var Service
     */
    private $_commonService;

    /**
     * User service instance
     *
     * @var UserService
     */
    private $_userService;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_commonService = $container->get(Service::class);
        $this->_userService   = $container->get(UserService::class);
        $this->_authorize     = $container->get('authorize');
    }

    /**
     * Gets list of user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function users(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query = EntityQuery::create(User::class, [[Group::class]]);
        $users = $this->_commonService->find($query);

        $data = array_map(function(User $user) {
            $data = [
                'user'   => $user,
                'groups' => $user->getGroups()->toArray(),
            ];
            return $data;
        }, $users);

        return $response->withJson($data);
    }

    /**
     * Gets current logged user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function get(Request $request, Response $response) {
        $token = $request->getHeader('X-User-Login-Token');

        if ($token && $token[0]) {
            $this->_userService->refreshLogin($token[0]);
        }

        return $response->withJson($this->_userService->getCurrentUser());
    }

    /**
     * Creates user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function create(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $data = $request->getParsedBody();

        $query = EntityQuery::create(User::class, [], ['username' => $data['user']['username']]);
        $user  = $this->_commonService->findOne($query);
        if (!$user) {
            $user = new User();
            $user->setUsername($data['user']['username']);
            $user->setPassword((Password::encrypt($data['user']['password']) ?? $data['user']['username']));
            $user->setToken(uniqid('user_token_'));

            $this->_commonService->persist($user);

            $query  = EntityQuery::create(Group::class, [], ['id' => array_column($data['groups'], 'id')]);
            $groups = $this->_commonService->find($query);
            $this->_commonService->assembleRelationsManyToMany($user, Group::class, $groups, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Updates user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function update(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $query = EntityQuery::create(User::class, [], ['username' => $data['user']['username']]);
        $user  = $this->_commonService->findOne($query);
        if ($user) {
            $user->setUsername($data['user']['username']);
            if (!empty($data['user']['password'])) {
                $user->setPassword(Password::encrypt($data['user']['password']));
            }

            $this->_commonService->persist($user);

            $query  = EntityQuery::create(Group::class, [], ['id' => array_column($data['groups'], 'id')]);
            $groups = $this->_commonService->find($query);
            $this->_commonService->assembleRelationsManyToMany($user, Group::class, $groups, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Self update user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function updateSelf(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_SETTINGS]);

        $userData = $this->_userService->getCurrentUser();
        $data     = $request->getParsedBody();
        if (!$userData || !$userData['user'] || $userData['user']->getId() !== $data['id']) {
            $response = Notification::withResponse($response, NotificationType::INVALID_REQUEST_DATA);
        } else if (!Password::verify(trim($data['oldPass']), $userData['user']->getPassword())) {
            $response = Notification::withResponse($response, NotificationType::INVALID_OLD_PASSWORD);
        } else if (trim(strlen($data['newPass'])) <= 4) {
            $response = Notification::withResponse($response, NotificationType::TOO_SHORT_PASSWORD);
        } else if ($data['newPass'] !== $data['newPassRepeat']) {
            $response = Notification::withResponse($response, NotificationType::NEW_PASSWORD_DONT_MATCH);
        } else {
            $query       = EntityQuery::create(User::class, [], ['id' => $data['id']]);
            $user        = $this->_commonService->findOne($query);
            $newPassword = trim($data['newPass']);
            $user->setPassword(Password::encrypt($newPassword));
            $this->_commonService->persist($user, true);
            $response    = $response->withStatus(HttpStatusCode::OK);
        }

        return $response;
    }

    /**
     * Deletes user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $params   Paramaters (id)
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, array $params) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query = EntityQuery::create(User::class, [], ['id' => $params['id']]);
        $user  = $this->_commonService->findOne($query);

        if ($user) {
            $this->_commonService->remove($user, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Generates API token
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $params   Parameters (id)
     *
     * @return Response
     */
    public function generateApiToken(Request $request, Response $response, array $params) {
        $this->_userService->generateApiToken($params['id']);

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Login user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function login(Request $request, Response $response) {
        if ($this->_userService->login($request->getParam('username'), $request->getParam('password'))) {
            $response = $response->withStatus(HttpStatusCode::OK);
        } else {
            $response = Notification::withResponse($response, NotificationType::WRONG_PASSWORD);
            $response = $response->withStatus(HttpStatusCode::UNAUTHORIZED);
        }

        return $response;
    }

    /**
     * Logout user
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function logout(Request $request, Response $response) {
        $user = $this->_userService->getCurrentUser();
        if ($user['user']) {
            $this->_userService->logout($user['user']->getUsername());
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

}
