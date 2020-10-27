<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\EmailQueue;
use App\PlanControl;
use App\PlanControlFactory;
use App\Points;
use App\ResultEnterControl;
use App\ResultEnterControlFactory;
use App\ResultOrderControl;
use App\ResultOrderControlFactory;
use App\Routes;
use App\StartControl;
use App\StartControlFactory;
use Contributte\RabbitMQ\Consumer\Consumer;
use Tracy\Dumper;

class RoutePresenter extends BasePresenter
{
    private Routes $routes;
    private Points $points;
    private Cups $cups;
    private PlanControlFactory $planControlFactory;
    private ResultEnterControlFactory $resultEnterControlFactory;
    private ResultOrderControlFactory $resultOrderControlFactory;
    private int $cupid;
    private int $routeid;

    public function __construct(Routes $routes, Points $points, PlanControlFactory $planControlFactory,
                                ResultEnterControlFactory $resultEnterControlFactory,
                                ResultOrderControlFactory $resultOrderControlFactory,
                                Cups $cups)
    {
        $this->routes = $routes;
        $this->points = $points;
        $this->cups = $cups;
        $this->planControlFactory = $planControlFactory;
        $this->resultEnterControlFactory = $resultEnterControlFactory;
        $this->resultOrderControlFactory = $resultOrderControlFactory;
        $this->cupid = $cups->getActive();
    }

    protected function createComponentPlanControl(): PlanControl
    {
        return $this->planControlFactory->create($this->cupid, $this->routeid, false);
    }

    protected function createComponentResultEnterControl(): ResultEnterControl
    {
        return $this->resultEnterControlFactory->create($this->cupid, $this->routeid);
    }

    protected function createComponentResultOrderControl(): ResultOrderControl
    {
        return $this->resultOrderControlFactory->create($this->cupid, $this->routeid);
    }


    public function actionDefault(int $id) {
        $this->routeid = $id;
        $route = $this->routes->find($id);
        if (is_null($route)) {
            $this->flashMessage('Trasa nenalezena');
            $this->redirect('Homepage:default');
        }
        $this->template->routes = $this->cups->find($this->cupid)->related('cups_routes');
        $this->template->route = $route;
    }
}