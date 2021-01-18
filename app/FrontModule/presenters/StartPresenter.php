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
        $onStart = function () {
            $this->redirect('this');
        };
        $onStop = function ($message) {
            $this->flashMessage($message, 'success');
            $this->redirect('Homepage:default');
        };
        return $this->startControlFactory->create($this->cupid, $onStart, $onStop);
    }

    public function actionDefault(): void
    {
        $now = new \DateTime();
        $this->template->allowed = $this->cups->isDateValid($this->cupid, $now, false);
    }
}