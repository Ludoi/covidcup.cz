<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: lysacup.cz
   Author:  Luděk Bednarz
*/


namespace App;



use Nette\Security\Permission;

class AuthorizatorFactory
{
    public static function create(): Permission
    {
        $acl = new Permission();

        self::addRoles($acl);
        self::addResources($acl);
        self::allow($acl);

        return $acl;
    }

    private static function addRoles(Permission $acl) {
        $acl->addRole('racer');
        $acl->addRole('admin', 'racer');
    }

    private static function addResources(Permission $acl) {
        $acl->addResource('users');
        $acl->addResource('settings');
        $acl->addResource('chat');
        $acl->addResource('stats');
        $acl->addResource('result');
        $acl->addResource('planning');
    }

    private static function allow(Permission $acl) {
        $acl->allow('racer', ['settings', 'chat', 'stats', 'result', 'planning'], ['view', 'edit']);
        $acl->allow('admin', Permission::ALL, array('view', 'edit', 'create', 'delete'));
    }
}