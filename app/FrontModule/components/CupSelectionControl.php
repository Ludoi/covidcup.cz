<?php
declare(strict_types=1);
/*
   Copyright (C) 2021 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Application\UI\Control;

class CupSelectionControl extends Control
{
    private int $cupid;
    private Cups $cups;
    private $onSelect;

    public function __construct(int $cupid, Cups $cups, callable $onSelect)
    {
        $this->cupid = $cupid;
        $this->cups = $cups;
        $this->onSelect = $onSelect;
    }

    public function handleSelect(int $id): void
    {
        $response = $this->getPresenter()->getHttpResponse();
        $response->setCookie('cupid', (string)$id, '1 hour');
        call_user_func($this->onSelect, $id);
        $this->redrawControl();
    }

    public function render(): void
    {
        $this->template->cups = $this->cups->findBy(['active' => true]);
        $this->template->selectedCup = $this->cupid;
        $this->template->render(__DIR__ . '/cupSelection.latte');
    }
}