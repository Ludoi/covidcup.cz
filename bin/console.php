<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/
#!/usr/bin/env php
// absolute filesystem path to this web root
define('WWW_DIR', __DIR__ . '/..');

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/app');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Bootstrap.php';

$container = \App\Bootstrap::boot()->createContainer();
$container->getService('database')->query("SET time_zone = ?;", date_default_timezone_get());
exit($container->getByType(Contributte\Console\Application::class)->run());