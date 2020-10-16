<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Routes;
use App\Users;

class StatisticsPresenter extends BasePresenter
{
    private Users $users;
    private Routes $routes;

    public function __construct(Users $users, Routes $routes)
    {
        $this->users = $users;
        $this->routes = $routes;
    }

    public function actionDefault() {
        $this->template->racersCount = $this->users->findBy(['active' => true])->count();
        $this->template->routesCount = $this->routes->findAll()->count();
    }
}