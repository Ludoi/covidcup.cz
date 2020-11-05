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

    public function insertItem(int $cupid, int $raceid, int $racerid, \DateTime $startTime, int $time, bool $guaranteed = false)
    {
        $now = new \DateTime();
        $this->insert(['cupid' => $cupid, 'raceid' => $raceid, 'racerid' => $racerid, 'created' => $now,
            'start_time' => $startTime, 'time_seconds' => $time, 'active' => true, 'guaranteed' => $guaranteed]);
    }

    public function getItems(int $cupid, ?bool $active, ?int $raceid, ?int $racerid): Selection
    {
        $filter = ['cupid' => $cupid];
        if (!is_null($active)) {
            $filter[] = ['active' => $active];
        }
        if (!is_null($raceid)) {
            $filter[] = ['raceid' => $raceid];
        }
        if (!is_null($racerid)) {
            $filter[] = ['racerid' => $racerid];
        }
        return $this->findBy($filter)->order('start_time DESC');
    }

    public function getOrderedItems(int $cupid, ?bool $active, int $raceid): Selection
    {
        $filter = ['cupid' => $cupid, 'raceid' => $raceid];
        if (!is_null($active)) {
            $filter[] = ['active' => $active];
        }
        return $this->findBy($filter)->group('racerid')->order('time_seconds ASC');
    }
}