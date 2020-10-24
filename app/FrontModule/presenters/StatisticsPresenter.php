<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\Routes;
use App\Users;

class StatisticsPresenter extends BasePresenter
{
    private Users $users;
    private Routes $routes;
    private Cups $cups;
    private int $cupid;

    public function __construct(Users $users, Routes $routes, Cups $cups)
    {
        $this->users = $users;
        $this->routes = $routes;
        $this->cups = $cups;
        $this->cupid = $cups->getActive();
    }

    public function actionDefault() {
        $this->template->racersCount = $this->users->findBy(['active' => true])->count();
        $this->template->maleCount = $this->users->findBy(['active' => true, 'gender' => 'm'])->count();
        $this->template->femaleCount = $this->users->findBy(['active' => true, 'gender' => 'f'])->count();
        $this->template->routesCount = $this->routes->findAll()->count();
    }
}