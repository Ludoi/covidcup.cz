<?php
// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

// absolute filesystem path to this web root
define('WWW_DIR', __DIR__);

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/app');

require APP_DIR . '/Bootstrap.php';
require WWW_DIR . '/vendor/autoload.php';

$configurator = \App\Bootstrap::boot();
$container = $configurator->createContainer();
$application = $container->getByType(Nette\Application\Application::class);
$application->run();
