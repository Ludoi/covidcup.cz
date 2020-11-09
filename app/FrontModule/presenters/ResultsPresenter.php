<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\ResultCupControl;
use App\ResultCupControlFactory;

class ResultsPresenter extends BaseSignPresenter
{
    private ResultCupControlFactory $resultCupControlFactory;
    private ?int $cupid;
    private Cups $cups;

    public function __construct(ResultCupControlFactory $resultCupControlFactory, Cups $cups)
    {
        $this->resultCupControlFactory = $resultCupControlFactory;
        $this->cups = $cups;
    }

    protected function createComponentResultCupControl(): ResultCupControl
    {
        return $this->resultCupControlFactory->create($this->cupid);
    }

    public function actionDefault(int $cupid = null): void
    {
        if (is_null($cupid)) {
            $this->cupid = $this->cups->getActive();
        } else {
            $this->cupid = $cupid;
        }
    }

}