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

exit(App\Bootstrap::boot()
    ->createContainer()
    ->getByType(Contributte\Console\Application::class)
    ->run());