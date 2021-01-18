<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\PlanControl;
use App\PlanControlFactory;
use App\Points;
use App\ResultEnterControl;
use App\ResultEnterControlFactory;
use App\ResultOrderControl;
use App\ResultOrderControlFactory;
use App\Results;
use App\Routes;

class RoutePresenter extends BasePresenter
{
    private Routes $routes;
    private Points $points;
    private Cups $cups;
    private PlanControlFactory $planControlFactory;
    private ResultEnterControlFactory $resultEnterControlFactory;
    private ResultOrderControlFactory $resultOrderControlFactory;
    private int $routeid;
    private int $raceid;
    private Results $results;

    public function __construct(Routes $routes, Points $points, PlanControlFactory $planControlFactory,
                                ResultEnterControlFactory $resultEnterControlFactory,
                                ResultOrderControlFactory $resultOrderControlFactory,
                                Cups $cups, Results $results)
    {
        $this->routes = $routes;
        $this->points = $points;
        $this->cups = $cups;
        $this->planControlFactory = $planControlFactory;
        $this->resultEnterControlFactory = $resultEnterControlFactory;
        $this->resultOrderControlFactory = $resultOrderControlFactory;
        $this->results = $results;
    }

    protected function createComponentPlanControl(): PlanControl
    {
        $onInsert[] = function () {
            $this->redirect('this');
        };
        return $this->planControlFactory->create($this->cupid, $this->raceid, false, $onInsert);
    }

    protected function createComponentResultEnterControl(): ResultEnterControl
    {
        $onInsert[] = function () {
            $this->redirect('this');
        };
        return $this->resultEnterControlFactory->create($this->cupid, $this->raceid, $onInsert);
    }

    protected function createComponentResultOrderControl(): ResultOrderControl
    {
        return $this->resultOrderControlFactory->create($this->cupid, $this->raceid);
    }


    public function actionDefault(int $id)
    {
        $this->routeid = $id;
        $route = $this->routes->find($id);
        if (is_null($route)) {
            $this->flashMessage('Trasa nenalezena');
            $this->redirect('Homepage:default');
        }
        $this->template->routes = $this->cups->find($this->cupid)->related('cups_routes');
        $this->template->route = $route;
        $categories = $this->cups->find($this->cupid)->related('categories');
        $this->template->times = [];
        $this->raceid = $this->cups->getRaceid($this->cupid, $this->routeid);
        foreach ($categories as $category) {
            $this->template->times[] = ['catid' => $category->catid, 'times' => $this->results->getStatistics($this->raceid, (int)$category->id)];
        }
    }
}