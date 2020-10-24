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
use Tracy\Dumper;

class ResultEnterControl extends Control
{
    private Results $results;
    private Users $users;
    private User $user;
    private Cups $cups;
    private int $cupid;
    private ?int $routeid;
    private int $itemsPerPage = 20;
    private int $page = 1;
    private bool $addItem = false;
    private int $userid;

    public function __construct(int $cupid, ?int $routeid, Results $results, Users $users, User $user, Cups $cups)
    {
        $this->cupid = $cupid;
        $this->routeid = $routeid;
        $this->results = $results;
        $this->users = $users;
        $this->user = $user;
        $this->cups = $cups;
        $this->userid = (int)$this->user->getId();
    }

    public function handlePage(int $page)
    {
        $this->page = $page;
        $this->redrawControl();
    }

    public function handleAddItem(): void
    {
        $this->addItem = true;
    }

    public function handleDelete(int $id): void
    {
        $this->results->find($id)->delete();
    }

    public function handleCancel(): void
    {
        $this->addItem = false;
    }

    public function createComponentAddItem(): Form
    {
        $form = new BootstrapForm();
//        if (!is_null($this->routeid)) {
//            $form->addHidden('routeid', $this->routeid);
//        } else {
//            $routes = [];
//            foreach ($this->cups->find($this->cupid)
//                         ->related('cups_routes', 'cupid')->fetchAll() as $route) {
//                $routes[] = [$route->id => $route->ref('routeid')->description];
//            };
//            $form->addSelect('routeid', 'Trasa:', $routes);
//        }
//        $form->addDateTime('plan_date', 'Termín:');
//        $form->addTextArea('comment', 'Komentář:');
//        $form->addSubmit('send', 'Přidat');
//        $form->onSubmit[] = [$this, 'processAddItem'];
        return $form;
    }

    public function render(): void
    {
        $items = $this->results->getItems($this->cupid, true, $this->routeid, $this->userid);
        $this->getPage($items);
        $this->template->addItem = $this->addItem;
        $this->template->userid = $this->userid;
        $this->template->routeid = $this->routeid;
        $this->template->cup = $this->cups->find($this->cupid);
        $now = new \DateTime( );
        $this->template->enterOpen = ($this->template->cup->valid_from->format('U') < $now->format('U')) &&
            ($this->template->cup->valid_to->format('U') > $now->format('U'));
        $this->template->render(__DIR__ . '/resultEnter.latte');
    }

    private function getPage(Selection $items)
    {
        $lastPage = 0;
        $this->template->items = $items->page($this->page, $this->itemsPerPage, $lastPage);
        $this->template->page = $this->page;
        $this->template->lastPage = $lastPage;
    }

    public function processAddItem(Form $form): void
    {
        $this->addItem = false;
//        $values = $form->getValues();
//        $this->plans->insertItem($this->cupid, $values->routeid, $this->userid, $values->comment, $values->plan_date);
//        $this->flashMessage('Plán uložen.');
        $this->redrawControl();
    }

}