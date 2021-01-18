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
use App\Followers;
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
    private CupsRacers $cupsRacers;
    private ResultEnterControlFactory $resultEnterControlFactory;
    private Followers $followers;

    public function __construct(Users $users, PlanControlFactory $planControlFactory,
                                ResultEnterControlFactory $resultEnterControlFactory, Cups $cups,
                                CupsRacers $cupsRacers, Followers $followers)
    {
        parent::__construct();
        $this->users = $users;
        $this->planControlFactory = $planControlFactory;
        $this->cups = $cups;
        $this->cupsRacers = $cupsRacers;
        $this->resultEnterControlFactory = $resultEnterControlFactory;
        $this->followers = $followers;
    }

    public function startup()
    {
        parent::startup();
        $this->userid = (int)$this->user->getId();
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

    public function handleFollow(int $racerFollow): void
    {
        $this->followers->insertItem($this->racerid, $racerFollow);
        $this->redirect('this');
    }

    public function handleUnfollow(int $racerFollow): void
    {
        $this->followers->removeFollower($this->racerid, $racerFollow);
        $this->redirect('this');
    }

    public function actionDefault(?int $id): void
    {
        if (is_null($id)) {
            $cupsRacer = $this->cupsRacers->findOneBy(['cups' => $this->cupid, 'userid' => $this->userid]);
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
        $this->template->myracerid = $this->racerid;
        $this->template->isFollowing = $this->followers->isFollowing($this->racerid, $this->template->racerid);
    }
}