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
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\User;

class StartControl extends Control
{
    private ?int $raceid = null;
    private Measurements $measurements;
    private Cups $cups;
    private User $user;
    private bool $firstPage = true;
    private bool $beforeStart;
    private Results $results;
    private Cache $cache;
    private $onStart;
    private $onStop;

    public function __construct(Cups $cups, Measurements $measurements, User $user, Results $results, IStorage $storage, callable $onStart,
                                callable $onStop)
    {
        $this->measurements = $measurements;
        $this->cups = $cups;
        $this->user = $user;
        $this->results = $results;
        $this->onStart = $onStart;
        $this->onStop = $onStop;
        $this->cache = new Cache($storage);
    }

    private function cleanCache(int $raceid): void
    {
        $this->cache->clean([Cache::TAGS => ['resultEnter', "resultEnter_$raceid", "resultOrder_$raceid"]]);
    }

    public function handleCancel(): void
    {
        $this->firstPage = true;
        $this->redrawControl();
    }

    public function handleStop(): void
    {
        $this->firstPage = false;
        $this->redrawControl();
    }

    public function createComponentPreStart(): Form
    {
        $form = new BootstrapForm();
        $cup = $this->cups->find($this->cups->getActive());
        $routes = [];
        foreach ($cup->related('cups_routes', 'cupid')->fetchAll() as $route) {
            $routes[$route->id] = $route->ref('routeid')->description;
        };
        $form->addSelect('raceid', 'Trasa:', $routes)->setPrompt('Vyber trasu')
            ->setRequired('Vyplň trasu.');
        $form->addProtection();
        $form->addSubmit('send', 'Chci startovat!');
        $form->onSubmit[] = [$this, 'processPreStart'];

        return $form;
    }

    public function processPreStart(Form $form): void
    {
        if ($form->isValid()) {
            $values = $form->getValues();
            $this->raceid = (int)$values->raceid;
            $this->firstPage = false;
        } else {
            $this->firstPage = true;
        }
        $this->redrawControl();
    }

    public function createComponentStart(): Form
    {
        $form = new BootstrapForm();
        $form->addHidden('raceid', $this->raceid);
        $form->addHidden('latitude')->setHtmlId('latitude');
        $form->addHidden('longitude')->setHtmlId('longitude');
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
            $startDistance = $this->cups->getDistance($this->cups->getActive(), (int)$values->raceid,
                $latitude, $longitude, true);
            $this->measurements->insertStart($racerid, (int)$values->raceid, $now, $latitude, $longitude, $startDistance);
            $this->flashMessage('Odstartováno!', 'success');
        }
        $this->firstPage = true;
        call_user_func($this->onStart);
        $this->redrawControl();
    }

    public function createComponentFinish(): Form
    {
        $form = new BootstrapForm();
        $form->addHidden('raceid', $this->raceid);
        $form->addHidden('latitude')->setHtmlId('latitude');
        $form->addHidden('longitude')->setHtmlId('longitude');
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
            $measurement = $this->measurements->findOneBy(['racerid' => $racerid, 'active' => true]);
            $finishDistance = $this->cups->getDistance($this->cups->getActive(), (int)$measurement->raceid,
                $latitude, $longitude, false);
            $measurementid = $this->measurements->updateFinish($racerid, $now, $latitude, $longitude, $finishDistance);
            if (!is_null($measurementid)) {
                $measurement = $this->measurements->find($measurementid);
                if (!is_null($measurement)) {
                    $duration = (int)$measurement->finish_time->format('U') - (int)$measurement->start_time->format('U');
                    $this->results->insert(['cupid' => $this->cups->getActive(), 'raceid' => $measurement->raceid,
                        'racerid' => $racerid, 'start_time' => $measurement->start_time, 'time_seconds' => $duration,
                        'created' => $now, 'active' => true, 'guaranteed' => true, 'measurementid' => $measurementid]);
                    $this->cleanCache((int)$measurement->raceid);
                }
            }
            $this->flashMessage('Ukončeno!', 'success');
            $timeStr = (ResultUtil::secondsTime($duration))->format('%h:%I:%S');
            $this->flashMessage("Dosažený čas je $timeStr.", 'success');
        }
        $this->firstPage = true;
        call_user_func($this->onStop, "Dosažený čas je $timeStr.");
        $this->redrawControl();
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