<?php

namespace SmartHome\Authorization;

use DI\Container;
use SmartHome\Entity\{
    Permission,
    User
};
use SlimSession\Helper as SessionHelper;
use Slim\Http\ServerRequest as Request;
use Doctrine\ORM\EntityRepository;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Authorize {

    /**
     * @var Container;
     */
    private $_container;

    public function __construct (Container $container) {
        $this->_container = $container;
    }

    public function isUserLoggedIn (Request $request) {
        $session = $this->_container->get('session'); /* @var $session SessionHelper */

        if (!$session->exists('user')) {
            throw new Exception($request, 'User is not logged in.');
        }

        return $this;
    }

    public function checkPermissions (Request $request, array $permissions, bool $all = true) {
        $this->isUserLoggedIn($request);

        $session = $this->_container->get('session'); /* @var $session SessionHelper */
        $user = $session->get('user');
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

    public function checkApiToken (Request $request): Authorize {
        $apiToken = $request->getAttribute('apiToken');
        $query = EntityQuery::create(User::class, [], ['apiToken' => $apiToken]);
        $find = $this->_container->get(Service::class)->findOne($query);

        if (empty($find) || empty($apiToken)) {
            throw new Exception($request, 'Invalid ApiToken.');
        }

        return $this;
    }

}
