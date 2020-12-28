<?php

use Doctrine\ORM\EntityManager;
use SmartHome\Entity;
use SmartHome\Enum\Permission;
use SmartHome\Common\Utils\{
    Password,
    Strings
};

require_once __DIR__.'/../bootstrap.php';

$container = $app->getContainer();
$em = $container->get('db'); /* @var $em EntityManager */

// ensure admin
$admin = $em->getRepository(Entity\User::class)->findOneBy(['username' => 'admin']);
if ($admin === null) {
    $admin = new Entity\User();
    $admin->setUsername('admin');
    $admin->setPassword(Password::encrypt('admin'));

    $em->persist($admin);
}

// ensure admin group
$adminGroup = $em->getRepository(Entity\Group::class)->find(1);
if ($adminGroup === null) {
    $adminGroup = new Entity\Group();
    $adminGroup->setName('Admin');

    $admin->getGroups()->add($adminGroup);
    $adminGroup->getUsers()->add($admin);

    $em->persist($adminGroup);
}

// ensure permissions
foreach ((new Permission())->getConstList() as $type) {
    $permission = $em->getRepository(Entity\Permission::class)->findOneBy(['type' => $type]);
    if ($permission === null) {
        $permission = new Entity\Permission();
        $permission->setName(Strings::fromUnderscore($type));
        $permission->setType($type);

        $adminGroup->getPermissions()->add($permission);
        $permission->getGroups()->add($adminGroup);

        $em->persist(($permission));
    }
}

$em->flush();
