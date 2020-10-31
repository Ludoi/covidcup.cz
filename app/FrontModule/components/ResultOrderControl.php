<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Application\UI\Control;
use Nette\Database\Table\Selection;
use Nette\Security\User;

class ResultOrderControl extends Control
{
    private Results $results;
    private Users $users;
    private User $user;
    private Cups $cups;
    private Routes $routes;
    private int $cupid;
    private int $routeid;
    private int $itemsPerPage = 30;
    private int $page = 1;
    private int $userid;

    public function __construct(int $cupid, int $routeid, Results $results, Users $users, User $user, Cups $cups, Routes $routes)
    {
        $this->cupid = $cupid;
        $this->routeid = $routeid;
        $this->results = $results;
        $this->users = $users;
        $this->user = $user;
        $this->cups = $cups;
        $this->routes = $routes;
        $this->userid = (int)$this->user->getId();
    }

    public function handlePage(int $page)
    {
        $this->page = $page;
        $this->redrawControl();
    }

    public function render(): void
    {
        $items = $this->results->getOrderedItems($this->cupid, true, $this->routeid);
        $this->getPage($items);
        $this->template->userid = $this->userid;
        $this->template->route = $this->cups->find($this->cupid)->related('cups_routes')
            ->where('id = ?', $this->routeid)->fetch()->ref('routeid');
        $this->template->render(__DIR__ . '/resultOrder.latte');
    }

    private function getPage(Selection $items)
    {
        $lastPage = 0;
        $this->template->items = $items->page($this->page, $this->itemsPerPage, $lastPage);
        $this->template->page = $this->page;
        $this->template->lastPage = $lastPage;
    }
}