<?php

namespace App;

use Contributte\ApiRouter\ApiRoute;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Routing\Router;

//use Nette\Application\Routers\CliRouter;
//use Nette\Application\Routers\Route;

/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * @return Router
     */
    public function createRouter(): Router
    {
        $router = new RouteList();
        $router->add(new Route('Homepage/setCupid/<cupid \d+>',
            ['module' => 'Front',
                'presenter' => [Route::VALUE => 'Homepage'],
                'action' => 'setCupid']));
        $router->add(new Route('<presenter>/<action>',
            ['module' => 'Front',
                'presenter' => [Route::VALUE => 'Homepage'],
                'action' => 'default']));
        return $router;
    }

}
