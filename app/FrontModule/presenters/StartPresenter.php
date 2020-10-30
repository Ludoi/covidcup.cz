<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\StartControl;
use App\StartControlFactory;

class StartPresenter extends BaseSignPresenter
{

    private StartControlFactory $startControlFactory;
    private Cups $cups;

    public function __construct(StartControlFactory $startControlFactory, Cups $cups)
    {
        $this->startControlFactory = $startControlFactory;
        $this->cups = $cups;
    }

    protected function createComponentStartControl(): StartControl
    {
        return $this->startControlFactory->create();
    }

    public function actionDefault(): void
    {
        $now = new \DateTime();
        $this->template->allowed = $this->cups->isDateValid($this->cups->getActive(), $now, false);
    }
}