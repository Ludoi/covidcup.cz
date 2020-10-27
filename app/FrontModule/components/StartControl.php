<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class StartControl extends Control
{
    private int $routeid;

    public function __construct(int $routeid)
    {
        $this->routeid = $routeid;
    }

    public function createComponentStart(): Form
    {
        $form = new BootstrapForm();
        $form->addHidden('routeid', $this->routeid);
        $form->addHidden('latitude');
        $form->addHidden('longitude');
        $form->addSubmit('send', 'Start');

        return $form;
    }

    public function render(): void
    {
        $this->template->render(__DIR__ . '/start.latte');
    }
}