<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\PlanControl;
use App\PlanControlFactory;
use App\Users;
use Nette\Security\User;

class RacerPresenter extends BaseSignPresenter
{
    private ?int $userid;
    private Users $users;
    private PlanControlFactory $planControlFactory;
    private Cups $cups;
    private int $cupid;

    public function __construct(Users $users, PlanControlFactory $planControlFactory, Cups $cups)
    {
        $this->users = $users;
        $this->planControlFactory = $planControlFactory;
        $this->cups = $cups;
        $this->cupid = $cups->getActive();
    }

    protected function createComponentPlanControl(): PlanControl
    {
        return $this->planControlFactory->create($this->cupid, null, false);
    }



    public function actionDefault(?int $id): void
    {
        if (is_null($id))
        {
            $user = $this->users->getUser($this->user->getId());
            $this->userid = (int)$user->id;
        } else {
            $this->userid = $id;
        }
    }
}