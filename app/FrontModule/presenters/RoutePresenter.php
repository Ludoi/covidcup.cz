<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\EmailQueue;
use App\Points;
use App\Routes;
use Contributte\RabbitMQ\Consumer\Consumer;
use Tracy\Dumper;

class RoutePresenter extends BasePresenter
{
    private Routes $routes;
    private Points $points;

    public function __construct(Routes $routes, Points $points)
    {
        $this->routes = $routes;
        $this->points = $points;
    }

    public function renderDefault(int $id) {
        $route = $this->routes->find($id);
        if (is_null($route)) {
            $this->flashMessage('Trasa nenalezena');
            $this->redirect('Homepage:default');
        }
        $this->template->routes = $this->routes->findAll();
        $this->template->route = $route;
    }
}