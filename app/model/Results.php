<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Database\Table\Selection;

class Results extends Table
{
    protected ?string $tableName = 'results';

    public function insertItem(int $cupid, int $routeid, int $userid, \DateTime $startTime, int $time, bool $guaranteed = false)
    {
        $now = new \DateTime();
        $this->insert(['cupid' => $cupid, 'routeid' => $routeid, 'userid' => $userid, 'created' => $now,
            'start_time' => $startTime, 'time_seconds' => $time, 'active' => true, 'guaranteed' => $guaranteed]);
    }

    public function getItems(int $cupid, ?bool $active, ?int $routeid, ?int $userid): Selection
    {
        $filter = ['cupid' => $cupid];
        if (!is_null($active)) {
            $filter[] = ['active' => $active];
        }
        if (!is_null($routeid)) {
            $filter[] = ['routeid' => $routeid];
        }
        if (!is_null($userid)) {
            $filter[] = ['userid' => $userid];
        }
        return $this->findBy($filter)->order('start_time DESC');
    }

    public function getOrderedItems(int $cupid, ?bool $active, int $routeid): Selection
    {
        $filter = ['cupid' => $cupid, 'routeid' => $routeid];
        if (!is_null($active)) {
            $filter[] = ['active' => $active];
        }
        return $this->findBy($filter)->group('userid')->order('time_seconds ASC');
    }
}