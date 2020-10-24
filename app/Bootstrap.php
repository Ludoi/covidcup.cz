<?php
declare(strict_types=1);

namespace App;

use Nette\Configurator;
use Tracy\Dumper;

class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator;
        // set umask to get correct permissions in cache
        umask(2);

        setlocale(LC_ALL, 'cs_CZ.utf8');

//        $configurator->setDebugMode('172.18.0.1');

// Enable Nette Debugger for error visualisation & logging
        $configurator->setDebugMode(true);
        $configurator->enableDebugger(APP_DIR . '/../log');

// Specify folder for cache
        $configurator->setTempDirectory(APP_DIR . '/../temp');

// Enable RobotLoader - this will load all classes automatically
        $configurator->createRobotLoader()
            ->addDirectory(APP_DIR)
            ->register();

// Create Dependency Injection container from config.neon file
        $configurator->addConfig(APP_DIR . '/config/config.neon');
        $configurator->addConfig(APP_DIR . '/config/config.local.neon'); // none section

        return $configurator;
    }
}