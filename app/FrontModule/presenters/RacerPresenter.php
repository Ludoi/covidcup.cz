<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\CupsRacers;
use App\PlanControl;
use App\PlanControlFactory;
use App\ResultEnterControl;
use App\ResultEnterControlFactory;
use App\Users;

class RacerPresenter extends BaseSignPresenter
{
    private ?int $userid;
    private Users $users;
    private PlanControlFactory $planControlFactory;
    private Cups $cups;
    private int $cupid;
    private CupsRacers $cupsRacers;
    private ResultEnterControlFactory $resultEnterControlFactory;

    public function __construct(Users $users, PlanControlFactory $planControlFactory,
                                ResultEnterControlFactory $resultEnterControlFactory, Cups $cups,
                                CupsRacers $cupsRacers)
    {
        $this->users = $users;
        $this->planControlFactory = $planControlFactory;
        $this->cups = $cups;
        $this->cupid = $cups->getActive();
        $this->cupsRacers = $cupsRacers;
        $this->resultEnterControlFactory = $resultEnterControlFactory;
    }

    protected function createComponentPlanControl(): PlanControl
    {
        return $this->planControlFactory->create($this->cupid, null, true);
    }

    protected function createComponentResultEnterControl(): ResultEnterControl
    {
        $onInsert[] = function () {
            $this->redirect('this');
        };
        return $this->resultEnterControlFactory->create($this->cupid, null, $onInsert);
    }

    public function actionDefault(?int $id): void
    {
        if (is_null($id)) {
            $user = $this->users->find((int)$this->user->getId());
            $this->userid = (int)$user->id;
            $cupsRacer = $this->cupsRacers->findOneBy(['cups' => $this->cups->getActive(), 'userid' => $this->userid]);
            if (is_null($cupsRacer)) {
                $this->flashMessage('Závodník nenalezen.');
                $this->redirect('Homepage:');
            }
        } else {
            $cupsRacer = $this->cupsRacers->find($id);
            if (is_null($cupsRacer)) {
                $this->flashMessage('Závodník nenalezen.');
                $this->redirect('Homepage:');
            }
            $this->userid = (int)$cupsRacer->userid;
            $user = $this->users->find($this->userid);
        }
        if (is_null($user)) {
            $this->flashMessage('Závodník nenalezen.');
            $this->redirect('Homepage:');
        }
        $this->template->userInfo = $user;
        $this->template->racerid = (int)$cupsRacer->id;
    }
}