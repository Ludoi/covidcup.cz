<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Measurements;
use App\StartControl;
use App\StartControlFactory;

class TestStartPresenter extends BaseSignPresenter
{

    private StartControlFactory $startControlFactory;
    private int $routeid;

    public function __construct(StartControlFactory $startControlFactory)
    {
        $this->startControlFactory = $startControlFactory;
        $this->routeid = 5;
    }

    protected function createComponentStartControl(): StartControl
    {
        return $this->startControlFactory->create($this->routeid);
    }

    public function actionDefault(): void
    {

    }
}