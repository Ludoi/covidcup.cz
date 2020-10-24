<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\ProfileControl;
use App\ProfileControlFactory;

class ProfilePresenter extends BaseSignPresenter
{
    private ProfileControlFactory $profileControlFactory;

    public function __construct(ProfileControlFactory $profileControlFactory)
    {
        $this->profileControlFactory = $profileControlFactory;
    }

    protected function createComponentProfileControl(): ProfileControl
    {
        return $this->profileControlFactory->create();
    }

    public function actionDefault(): void
    {

    }
}