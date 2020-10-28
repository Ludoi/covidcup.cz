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
use Nette\Database\Table\Selection;
use Nette\Security\User;

class ChatControl extends Control
{
    private Chats $chats;
    private Users $users;
    private User $user;
    private Cups $cups;
    private int $cupid;
    private int $itemsPerPage = 5;
    private int $page = 1;
    private bool $addItem = false;

    public function __construct(int $cupid, Chats $chats, Users $users, User $user, Cups $cups)
    {
        $this->cupid = $cupid;
        $this->chats = $chats;
        $this->users = $users;
        $this->user = $user;
        $this->cups = $cups;
    }

    public function handlePage(int $page): void
    {
        $this->page = $page;
        $this->redrawControl();
    }

    public function handleAddItem(): void
    {
        $this->addItem = true;
    }

    public function createComponentAddItem(): Form
    {
        $form = new BootstrapForm();
        $form->addTextArea('content', 'Příspěvek:')->setRequired();
        $form->addSubmit('send', 'Přidat');
        $form->addProtection();
        $form->onSubmit[] = [$this, 'processAddItem'];
        return $form;
    }

    public function render(): void
    {
        $items = $this->chats->getItems($this->cupid, true);
        $this->getPage($items);
        $this->template->addItem = $this->addItem;
        $this->template->render(__DIR__ . '/chats.latte');
    }

    private function getPage(Selection $items): void
    {
        $lastPage = 0;
        $this->template->items = $items->page($this->page, $this->itemsPerPage, $lastPage);
        $this->template->page = $this->page;
        $this->template->lastPage = $lastPage;
    }

    public function processAddItem(Form $form): void
    {
        $this->addItem = false;
        if ($form->isValid()) {
            $values = $form->getValues();
            $userid = (int)$this->user->getId();
            $racerid = $this->cups->getRacerid($this->cupid, $userid);
            if (!is_null($racerid)) {
                $this->chats->insertItem($this->cupid, $racerid, $values->content, '');
                $this->flashMessage('Příspěvek uložen.');
            }
        }
        $this->redrawControl();
    }
}