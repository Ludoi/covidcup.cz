<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\Results;
use App\Routes;
use App\Users;

class StatisticsPresenter extends BasePresenter
{
    private Users $users;
    private Routes $routes;
    private Cups $cups;
    private Results $results;

    public function __construct(Users $users, Routes $routes, Cups $cups, Results $results)
    {
        $this->users = $users;
        $this->routes = $routes;
        $this->cups = $cups;
        $this->results = $results;
    }

    public function actionDefault() {
        $this->template->cup = $this->selectedCup;
        $this->template->cacheid = 'statistics-main';
        $this->template->racersCount = $this->users->findBy(['active' => true])->count();
        $this->template->maleCount = $this->users->findBy(['active' => true, 'gender' => 'm'])->count();
        $this->template->femaleCount = $this->users->findBy(['active' => true, 'gender' => 'f'])->count();
        $this->template->routesCount = $this->routes->findAll()->count();
        $this->template->resultsCount = $this->results->findBy(['cupid' => $this->cupid])->count();
        $year = (int)(new \DateTime())->format('Y');
        $this->template->averageAge = $year - $this->users->findAll()->aggregation('AVG(year)');
        $racersCount = $this->results->findBy(['cupid' => $this->cupid])->group('racerid')->count();
        $this->template->averageRaces = ($racersCount > 0) ? $this->template->resultsCount / $racersCount : 0;
    }
}