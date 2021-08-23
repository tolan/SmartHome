<?php

namespace SmartHome\Authorization;

use DI\Container;
use SmartHome\Entity\{
    Permission,
    User
};
use SmartHome\Cache\Storage;
use Slim\Http\ServerRequest as Request;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;

/**
 * This file defines class for authorization
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Authorize {

    /**
     * Container
     *
     * @var Container;
     */
    private $_container;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_container = $container;
    }

    /**
     * Checks that the user is logged
     *
     * @param Request $request Request
     *
     * @return Authorize
     *
     * @throws Exception
     */
    public function isUserLoggedIn(Request $request): Authorize {
        $session = $this->_container->get('session'); /* @var $session Storage */

        if (!$session->has('user')) {
            throw new Exception($request, 'User is not logged in.');
        }

        return $this;
    }

    /**
     * Checks that the logged user has permissions
     *
     * @param Request $request     Requests
     * @param array   $permissions Set of permission/s
     * @param bool    $all         All or some
     *
     * @return Authorize
     *
     * @throws Exception
     */
    public function checkPermissions(Request $request, array $permissions, bool $all = true): Authorize {
        $this->isUserLoggedIn($request);

        $session         = $this->_container->get('session'); /* @var $session Storage */
        $user            = unserialize($session->get('user'));
        $userPermissions = array_map(function(Permission $permission) {
            return $permission->getType();
        }, $user['permissions']);

        $valid = false;
        if ($all) {
            $valid = array_values(array_intersect($userPermissions, $permissions)) === $permissions;
        } else {
            $valid = count(array_intersect($userPermissions, $permissions));
        }

        if (!$valid) {
            throw new Exception($request, 'Insufficient permissions');
        }

        return $this;
    }

    /**
     * Checks validation of user api token
     *
     * @param Request $request Request
     *
     * @return Authorize
     *
     * @throws Exception
     */
    public function checkApiToken(Request $request): Authorize {
        $apiToken = $request->getAttribute('apiToken');
        $query    = EntityQuery::create(User::class, [], ['apiToken' => $apiToken]);
        $find     = $this->_container->get(Service::class)->findOne($query);

        if (empty($find) || empty($apiToken)) {
            throw new Exception($request, 'Invalid ApiToken.');
        }

        return $this;
    }

}
