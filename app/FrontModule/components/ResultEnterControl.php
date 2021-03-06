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
use Nette\Database\Table\Selection;
use Nette\Security\User;

class ResultEnterControl extends Control
{
    private Results $results;
    private Users $users;
    private User $user;
    private Cups $cups;
    private Cache $cache;
    private int $cupid;
    private ?int $raceid;
    private int $itemsPerPage = 20;
    private int $page = 1;
    private bool $addItem = false;
    private bool $addItemGPX = false;
    private int $userid;
    private int $racerid;
    private GPXQueue $GPXQueue;
    private array $onInsert = [];

    public function __construct(int $cupid, ?int $raceid, Results $results, Users $users, User $user, Cups $cups,
                                GPXQueue $GPXQueue, IStorage $storage, array $onInsert)
    {
        $this->cupid = $cupid;
        $this->raceid = $raceid;
        $this->results = $results;
        $this->users = $users;
        $this->user = $user;
        $this->cups = $cups;
        $this->userid = (int)$this->user->getId();
        $this->racerid = $this->cups->getRacerid($this->cupid, $this->userid);
        $this->GPXQueue = $GPXQueue;
        $this->cache = new Cache($storage);
        $this->onInsert = $onInsert;
    }

    private function cleanCache(int $raceid): void
    {
        $this->cache->clean([Cache::TAGS => ['resultEnter', "resultEnter_$raceid", "resultOrder_$raceid"]]);
    }

    public function handlePage(int $page)
    {
        $this->page = $page;
        $this->redrawControl();
    }

    public function handleAddItem(): void
    {
        $this->addItem = true;
        $this->addItemGPX = false;
    }

    public function handleAddItemGPX(): void
    {
        $this->addItem = false;
        $this->addItemGPX = true;
    }

    public function handleDelete(int $id): void
    {
        $result = $this->results->find($id);
        if (!is_null($result)) {
            $userid = $result->ref('racerid')->userid;
            if ($userid == $this->userid) {
                $raceid = (int)$result->raceid;
                if (!is_null($result->measurementid)) {
                    $result->ref('measurement')->delete();
                } else {
                    $result->delete();
                }
                $this->cleanCache($raceid);
            }
        }
    }

    public function handleCancel(): void
    {
        $this->addItem = false;
        $this->addItemGPX = false;
    }

    public function createComponentAddItemGPX(): Form
    {
        $form = new BootstrapForm();
        if (!is_null($this->raceid)) {
            $form->addHidden('raceid', $this->raceid);
        } else {
            $routes = [];
            foreach ($this->cups->find($this->cupid)
                         ->related('cups_routes', 'cupid')->fetchAll() as $route) {
                $routes[$route->id] = $route->ref('routeid')->description;
            };
            $form->addSelect('raceid', 'Trasa:', $routes)->setRequired(true)->setPrompt('vyber trasu');
        }
        $form->addUpload('gpxFile', 'Soubor GPX:')->getControlPrototype()->setAttribute('class', 'form-control-file');
        $form->addProtection();
        $form->addSubmit('send', 'Přidat');
        $form->onSubmit[] = [$this, 'processAddItemGPX'];

        return $form;
    }

    public function processAddItemGPX(Form $form): void
    {
        $this->addItemGPX = false;
        if ($form->isValid()) {
            $values = $form->getValues();
            $now = new \DateTime();
            $filename = sprintf('result_%05d-%05d_%s.gpx', $this->racerid, $values->raceid, $now->format('Y-m-d-H-i-s'));
            $moveDir = APP_DIR . '/../files/gpx/results/' . $filename;
            $values->gpxFile->move($moveDir);
            $this->GPXQueue->publish($this->cupid, $this->racerid, (int)$values->raceid, $filename);
        }
        if (!$form->hasErrors()) {
            $this->flashMessage('Soubor uložen ke zpracování.');
            $this->onInsert;
        }
        $this->redrawControl();
    }

    public function createComponentAddItem(): Form
    {
        $form = new BootstrapForm();
        if (!is_null($this->raceid)) {
            $form->addHidden('raceid', $this->raceid);
        } else {
            $routes = [];
            foreach ($this->cups->find($this->cupid)
                         ->related('cups_routes', 'cupid')->fetchAll() as $route) {
                $routes[$route->id] = $route->ref('routeid')->description;
            };
            $form->addSelect('raceid', 'Trasa:', $routes)->setRequired(true)->setPrompt('vyber trasu');
        }
        $form->addDateTime('startTime', 'Čas startu:')->setRequired(true)->getControlPrototype()
            ->setAttribute('data-target', '#startTimePicker')
            ->setAttribute('id', 'startTimePicker')->setAttribute('data-toggle', 'datetimepicker');;
        $form->addText('time', 'Dosažený čas:')->setRequired(true)->setEmptyValue('0:00:00')
            ->addRule($form::PATTERN, 'Špatná hodnota', '[0-9]:[0-5][0-9]:[0-5][0-9]');
        $form->addProtection();
        $form->addSubmit('send', 'Přidat');
        $form->onSubmit[] = [$this, 'processAddItem'];

        return $form;
    }

    public function processAddItem(Form $form): void
    {
        if ($form->isValid()) {
            $values = $form->getValues();
            if (!$this->cups->isDateValid($this->cupid, $values->startTime, true)) {
                $form->addError('Čas startu není v době konání poháru.');
            } else {
                $parts = explode(':', $values->time);
                $time = (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
                $correct = $this->results->isItemCorrect($this->cupid, (int)$values->raceid,
                    $this->racerid, $values->startTime, $time);
                if ($correct) {
                    $this->results->insertItem($this->cupid, (int)$values->raceid, $this->racerid, $values->startTime, $time);
                    $this->cleanCache((int)$values->raceid);
                } else {
                    $form->addError('Výsledek s podobným časem startu už je uložen');
                }
            }
        }
        $this->addItem = $form->hasErrors();
        if (!$form->hasErrors()) {
            $this->flashMessage('Výsledek uložen.', 'success');
            $this->onInsert;
        }
        $this->redrawControl();
    }

    public function render(?int $racerid = null): void
    {
        $items = $this->results->getItems($this->cupid, true, $this->raceid, $racerid);
        $this->getPage($items);
        $this->template->addItem = $this->addItem;
        $this->template->addItemGPX = $this->addItemGPX;
        $this->template->userid = $this->userid;
        $this->template->racerid = $this->racerid;
        $this->template->raceid = $this->raceid;
        $this->template->cup = $this->cups->find($this->cupid);
        $now = new \DateTime();
        $this->template->enterOpen = $this->cups->isDateValid($this->cupid, $now, false);
        $this->template->form = $this['addItem'];
        $this->template->render(__DIR__ . '/resultEnter.latte');
    }

    public function renderAddItem(): void
    {
        $this->template->addItem = $this->addItem;
        $this->template->addItemGPX = $this->addItemGPX;
        $this->template->userid = $this->userid;
        $this->template->raceid = $this->raceid;
        $this->template->cup = $this->cups->find($this->cupid);
        $now = new \DateTime();
        $this->template->enterOpen = $this->cups->isDateValid($this->cupid, $now, false);
        $this->template->form = $this['addItem'];
        $this->template->render(__DIR__ . '/resultEnterSingle.latte');
    }

    private function getPage(Selection $items)
    {
        $lastPage = 0;
        $this->template->items = $items->page($this->page, $this->itemsPerPage, $lastPage);
        $this->template->page = $this->page;
        $this->template->lastPage = $lastPage;
    }

}