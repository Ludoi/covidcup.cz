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

class PlanControl extends Control
{
    private Plans $plans;
    private Users $users;
    private User $user;
    private Cups $cups;
    private int $cupid;
    private ?int $raceid;
    private int $itemsPerPage = 10;
    private int $page = 1;
    private bool $addItem = false;
    private int $userid;
    private int $racerid;
    private bool $onlyOwn;

    public function __construct(int $cupid, ?int $raceid, bool $onlyOwn, Plans $plans, Users $users, User $user, Cups $cups)
    {
        $this->cupid = $cupid;
        $this->raceid = $raceid;
        $this->plans = $plans;
        $this->users = $users;
        $this->user = $user;
        $this->cups = $cups;
        $this->onlyOwn = $onlyOwn;
        $this->userid = (int)$this->user->getId();
        $this->racerid = $this->cups->getRacerid($this->cups->getActive(), $this->userid);
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
        $plan = $this->plans->find($id);
        if (!is_null($plan)) {
            $userid = $plan->ref('racerid')->userid;
            if ($userid == $this->userid) {
                $plan->delete();
            }
        }
    }

    public function handleCancel(): void
    {
        $this->addItem = false;
    }

    public function createComponentAddItem(): Form
    {
        $form = new BootstrapForm();
        $cup = $this->cups->find($this->cupid);
        if (!is_null($this->raceid)) {
            $form->addHidden('raceid', $this->raceid);
        } else {
            $routes = [];
            foreach ($cup->related('cups_routes', 'cupid')->fetchAll() as $route) {
                $routes[$route->id] = $route->ref('routeid')->description;
            };
            $form->addSelect('raceid', 'Trasa:', $routes)->setPrompt('Vyber trasu')
                ->setRequired('Vyplň trasu.');
        }

        $form->addDateTime('plan_date', 'Termín:')->getControlPrototype()->setAttribute('data-target', '#planDatePicker')
            ->setAttribute('id', 'planDatePicker')->setAttribute('data-toggle', 'datetimepicker');
        $form->addProtection();
        $form->addTextArea('comment', 'Komentář:');
        $form->addSubmit('send', 'Přidat');
        $form->onSubmit[] = [$this, 'processAddItem'];
        return $form;
    }

    public function render(): void
    {
        $items = $this->plans->getItems($this->cupid, true, $this->raceid,
            $this->onlyOwn ? $this->racerid : null);
        $this->getPage($items);
        $this->template->addItem = $this->addItem;
        $this->template->userid = $this->userid;
        $this->template->racerid = $this->racerid;
        $this->template->raceid = $this->raceid;
        $this->template->render(__DIR__ . '/plans.latte');
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
        $values = $form->getValues();
        $cup = $this->cups->find($this->cupid);
        if (!is_null($cup)) {
            if ($values->plan_date->format('U') < $cup->valid_from->format('U') ||
                $values->plan_date->format('U') > $cup->valid_to->format('U')) {
                $form['plan_date']->addError("Termín musí být v rozsahu {$cup->valid_from->format('j.n.Y, H:i')} a {$cup->valid_to->format('j.n.Y, H:i')}");
            } else {
                if (!is_null($this->racerid)) {
                    $this->addItem = false;
                    $this->plans->insertItem($this->cupid, (int)$values->raceid, $this->racerid,
                        $values->comment, $values->plan_date);
                    $this->flashMessage('Plán uložen.');
                }
            }
        }
        $this->redrawControl();
    }

}