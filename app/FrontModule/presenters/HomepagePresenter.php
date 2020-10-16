<?php
declare(strict_types=1);

namespace FrontModule;

use App\Routes;

/**
 * Homepage presenter.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
class HomepagePresenter extends BasePresenter {
    private Routes $routes;

    public function __construct(Routes $routes)
    {
        $this->routes = $routes;
    }

    public function actionRules() {
        $this->template->routes = $this->routes->findAll();
    }
}
