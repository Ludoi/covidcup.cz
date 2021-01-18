<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\ResultEnterControl;
use App\ResultEnterControlFactory;

class EnterResultPresenter extends BaseSignPresenter
{
    private ResultEnterControlFactory $resultEnterControlFactory;
    private Cups $cups;

    public function __construct(Cups $cups, ResultEnterControlFactory $resultEnterControlFactory)
    {
        $this->resultEnterControlFactory = $resultEnterControlFactory;
        $this->cups = $cups;
    }

    protected function createComponentResultEnterControl(): ResultEnterControl
    {
        $onInsert[] = function () {
            $this->redirect('this');
        };
        return $this->resultEnterControlFactory->create($this->cupid, null, $onInsert);
    }

    public function actionDefault(): void
    {

    }
}