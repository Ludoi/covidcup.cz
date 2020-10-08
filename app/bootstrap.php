<?php

use Nette\Configurator;
use Nette\Security\Permission;

// set umask to get correct permissions in cache
umask(2);

setlocale(LC_ALL, 'cs_CZ.utf8');

$configurator = new Configurator;

$configurator->setDebugMode('172.18.0.1');

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode(true);
$configurator->enableDebugger(__DIR__ . '/../log');

// Specify folder for cache
$configurator->setTempDirectory(__DIR__ . '/../temp');

// Enable RobotLoader - this will load all classes automatically
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon'); // none section
$container = $configurator->createContainer();

// define ACL
//$acl = new Permission;
//
//$acl->addRole('owner');
//$acl->addRole('administrator', 'owner');
//
//$acl->addResource('race');
//$acl->addResource('myrace');
//$acl->addResource('racers');
//$acl->addResource('users');
//$acl->addResource('chips');
//$acl->addResource('test');
//$acl->addResource('measure');
//
//$acl->allow('owner', array('myrace', 'racers'), array('view', 'edit'));
//$acl->allow('administrator', Permission::ALL, array('view', 'edit', 'create', 'delete'));
//
//$user = $container->getService('user');
//$user->setAuthorizator($acl);

return $container;
