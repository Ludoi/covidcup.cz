<?php

namespace App;

use Contributte\ApiRouter\ApiRoute;
//use Nette\Application\Routers\CliRouter;
//use Nette\Application\Routers\Route;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Routing\Router;

/**
 * Router factory.
 */
class RouterFactory {

    /**
     * @return Router
     */
    public function createRouter(): Router {
        $router = new RouteList();
        $router->add(new Route('<presenter>/<action>',
            [ 'module' => 'Front',
              'presenter' => [ Route::VALUE => 'Homepage'],
              'action' => 'default' ]));
//        if (PHP_SAPI == 'cli') {
//            $router[] = new CliRouter();
//        } else {
//            $router[] = new ApiRoute('/api/race/<id>', 'Api:Race', [ 'parameters' => [ 'id' => ['requirement' => '\d+'] ], 'methods' => [ 'GET' ] ]);
//            $router[] = new Route('previews/<file>', array(
//                'module' => 'Front',
//                'presenter' => 'Image',
//                'action' => 'preview'));
//// new routes
//            $router[] = new Route('admin/<presenter>/<action>[/<id \d+>]', array(
//                'module' => 'Admin',
//                'presenter' => array(Route::VALUE => 'Homepage',
//                    Route::FILTER_TABLE => array(
//                        'clanek' => 'Article',
//                        'diskuse' => 'Talk',
//                        'trasa' => 'Route',
//                        'kontakty' => 'Contacts',
//                        'vysledky' => 'Result',
//                        'prihlaseni' => 'Sign',
//                        'zavodnik' => 'Racer',
//                        'rocnik' => 'Cup',
//                        'zavod' => 'Race',
//                        'tym' => 'Team',
//                        'jednorazove' => 'OneTime',
//                        'hlasovani' => 'Poll',
//                        'spolujizda' => 'Commuting',
//                        'tlustosi' => 'Tir',
//                        'predplatne' => 'Subscription'
//                    )),
//                'action' => array(
//                    Route::VALUE => 'default',
//                    Route::FILTER_TABLE => array(
//                        'ukaz' => 'display',
//                        'etapa' => 'race',
//                        'rocnik' => 'cup',
//                        'zmenit' => 'change',
//                        'rozpis-etap' => 'calendar',
//                        'jak-to-bylo' => 'history',
//                        'jak-to-bylo-tymy' => 'history-team',
//                        'zavodnici' => 'racers',
//                        'skokan-roku' => 'skippers',
//                        'propadak-roku' => 'droppers',
//                        'tymy' => 'teams',
//                        'kontakty' => 'contacts',
//                        'porovnani-souperu' => 'comparison',
//                        'porovnani-etap' => 'race-comparison'
//                    )),
//                'id' => NULL,
//                    ));
//            $router[] = new Route('<presenter>/<action>[/<id \d+>]', array(
//                'module' => 'Front',
//                'presenter' => array(Route::VALUE => 'Homepage',
//                    Route::FILTER_TABLE => array(
//                        'clanek' => 'Article',
//                        'diskuse' => 'Talk',
//                        'trasa' => 'Route',
//                        'kontakty' => 'Contacts',
//                        'vysledky' => 'Result',
//                        'prihlaseni' => 'Sign',
//                        'zavodnik' => 'Racer',
//                        'rocnik' => 'Cup',
//                        'zavod' => 'Race',
//                        'tym' => 'Team',
//                        'obrazek' => 'Image',
//                        'hlasovani' => 'Poll',
//                        'predplatne' => 'Subscription',
//                        'spolujizda' => 'Commuting'
//                    )),
//                'action' => array(
//                    Route::VALUE => 'default',
//                    Route::FILTER_TABLE => array(
//                        'ukaz' => 'display',
//                        'etapa' => 'race',
//                        'rocnik' => 'cup',
//                        'zmenit' => 'change',
//                        'rozpis-etap' => 'calendar',
//                        'jak-to-bylo' => 'history',
//                        'jak-to-bylo-tymy' => 'history-team',
//                        'zavodnici' => 'racers',
//                        'skokan-roku' => 'skippers',
//                        'propadak-roku' => 'droppers',
//                        'tymy' => 'teams',
//                        'kontakty' => 'contacts',
//                        'nahled' => 'preview',
//                        'srovnani' => 'comparison',
//                        'smazat' => 'delete',
//                        'porovnani-etap' => 'race-comparison'
//                    )),
//                'id' => NULL,
//                    ));
//        }
        return $router;
    }

}
