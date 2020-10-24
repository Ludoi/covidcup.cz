<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tracy\Dumper;

class ProfileControl extends Control
{
    private Users $users;
    private User $user;
    private UsersChanges $usersChanges;
    private bool $changeItem;

    public function __construct(Users $users, User $user, UsersChanges $usersChanges)
    {
        $this->users = $users;
        $this->user = $user;
        $this->usersChanges = $usersChanges;
        $this->changeItem = false;
    }

    protected function createComponentChangeProfile() {
        $now = new DateTime;
        $thisYear = (int)$now->format('Y');
        $lowestYear = $thisYear - 100;
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::VERTICAL_MODE;
        $form->addText('firstname', 'Jméno:', 30, 50)->setRequired('Vyplň jméno.')
            ->addRule(Form::FILLED, 'Jméno je povinné.');
        $form->addText('lastname', 'Příjmení:', 50, 50)->setRequired('Vyplň příjmení.')
            ->addRule(Form::FILLED, 'Příjmení je povinné.');
        $form->addText('nickname', 'Přezdívka:', 50, 50)
            ->addRule(Form::FILLED, 'Jméno do výsledků je povinné.');
        $form->addText('email', 'Email:', 50, 250)->addRule(Form::EMAIL)->setRequired();

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'saveProfile'];
        return $form;
    }

    public function handleChange(): void
    {
        $this->changeItem = true;
        $this->redrawControl();
    }

    public function handleCancel(): void
    {
        $this->changeItem = false;
        $this->redrawControl();
    }

    public function render(): void
    {
        $this->template->changeItem = $this->changeItem;
        $userprofile = $this->users->find((int)$this->user->getId());
        $this->template->userprofile = $userprofile;
        if ($this->changeItem)
        {
            $form = $this->getComponent('changeProfile');
            $form->setDefaults([ 'firstname' => $userprofile->firstname, 'lastname' => $userprofile->lastname,
                'nickname' => $userprofile->nickname, 'email' => $userprofile->email ]);
        }
        $this->template->render(__DIR__ . '/profile.latte');
    }

    public function saveProfile(Form $form): void
    {
        $this->changeItem = false;
        $values = $form->getValues();
        $userprofile = $this->users->find((int)$this->user->getId());
        $oldProfile = $userprofile->toArray();
        $this->usersChanges->insert($oldProfile);
        $values['updated'] = new DateTime();
        $userprofile->update($values);

        $this->flashMessage('Profil uložen.');
        $this->redrawControl();
    }
}