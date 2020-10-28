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
use Nette\Security\User;

class StartControl extends Control
{
    private int $routeid;
    private Measurements $measurements;
    private Cups $cups;
    private User $user;
    private bool $firstPage = true;
    private bool $beforeStart;
    private Results $results;

    public function __construct(int $routeid, Cups $cups, Measurements $measurements, User $user, Results $results)
    {
        $this->routeid = $routeid;
        $this->measurements = $measurements;
        $this->cups = $cups;
        $this->user = $user;
        $this->results = $results;
    }

    public function handleCancel(): void
    {
        $this->firstPage = true;
    }

    public function handleStart(): void
    {
        $this->firstPage = false;

    }

    public function handleStop(): void
    {
        $this->firstPage = false;
    }

    public function createComponentStart(): Form
    {
        $form = new BootstrapForm();
        $form->addHidden('routeid', $this->routeid);
        $form->addHidden('latitude')->setHtmlId('start-latitude');
        $form->addHidden('longitude')->setHtmlId('start-longitude');
        $form->addProtection();
        $form->addSubmit('send', 'Start!')->getControlPrototype()->setAttribute('class', 'btn btn-block btn-lg btn-danger');
        $form->onSubmit[] = [$this, 'processStart'];

        return $form;
    }

    public function processStart(Form $form): void
    {
        if ($form->isValid()) {
            $values = $form->getValues();
            $now = new \DateTime();
            $userid = (int)$this->user->getId();
            $racerid = $this->cups->getRacerid($this->cups->getActive(), $userid);
            $latitude = ($values->latitude != '') ? (float)$values->latitude : null;
            $longitude = ($values->longitude != '') ? (float)$values->longitude : null;
            $this->measurements->insertStart($racerid, $this->routeid, $now, $latitude, $longitude);
        }
    }

    public function createComponentFinish(): Form
    {
        $form = new BootstrapForm();
        $form->addHidden('routeid', $this->routeid);
        $form->addHidden('latitude')->setHtmlId('finish-latitude');
        $form->addHidden('longitude')->setHtmlId('finish-longitude');
        $form->addProtection();
        $form->addSubmit('send', 'Stop!')->getControlPrototype()->setAttribute('class', 'btn btn-block btn-lg btn-danger');
        $form->onSubmit[] = [$this, 'processFinish'];

        return $form;
    }

    public function processFinish(Form $form): void
    {
        if ($form->isValid()) {
            $values = $form->getValues();
            $now = new \DateTime();
            $userid = (int)$this->user->getId();
            $racerid = $this->cups->getRacerid($this->cups->getActive(), $userid);
            $latitude = ($values->latitude != '') ? (float)$values->latitude : null;
            $longitude = ($values->longitude != '') ? (float)$values->longitude : null;
            $measurementid = $this->measurements->updateFinish($racerid, $now, $latitude, $longitude);
            if (!is_null($measurementid)) {
                $measurement = $this->measurements->find($measurementid);
                if (!is_null($measurement)) {
                    $duration = (int)$measurement->finish_time->format('U') - (int)$measurement->start_time->format('U');
                    $this->results->insert(['cupid' => $this->cups->getActive(), 'routeid' => $measurement->routeid,
                        'userid' => $racerid, 'start_time' => $measurement->start_time, 'time_seconds' => $duration,
                        'created' => $now, 'active' => true, 'guaranteed' => true, 'measurementid' => $measurementid]);
                }
            }
        }
    }

    private function checkIsActive(): void
    {
        $userid = (int)$this->user->getId();
        $racerid = $this->cups->getRacerid($this->cups->getActive(), $userid);
        $isActive = $this->measurements->isActive($racerid);
        $this->beforeStart = !$isActive;
    }

    public function render(): void
    {
        $this->checkIsActive();
        $this->template->firstPage = $this->firstPage;
        $this->template->beforeStart = $this->beforeStart;
        $this->template->render(__DIR__ . '/start.latte');
    }
}