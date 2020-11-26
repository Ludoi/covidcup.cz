<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use DateInterval;
use DateTime;
use Nette\Database\Table\Selection;

class Results extends Table
{
    protected ?string $tableName = 'results';

    public function insertItem(int $cupid, int $raceid, int $racerid, DateTime $startTime, int $time,
                               bool $guaranteed = false, ?int $measurementid = null)
    {
        $now = new DateTime();
        $finishTime = clone $startTime;
        $finishTime->add(new DateInterval('PT' . $time . 'S'));
        $this->insert(['cupid' => $cupid, 'raceid' => $raceid, 'racerid' => $racerid, 'created' => $now,
            'start_time' => $startTime, 'finish_time' => $finishTime, 'time_seconds' => $time, 'active' => true,
            'guaranteed' => $guaranteed, 'measurementid' => $measurementid]);
    }

    public function isItemCorrect(int $cupid, int $raceid, int $racerid, DateTime $startTime, int $time): bool
    {
        $finishTime = clone $startTime;
        $finishTime->add(new DateInterval('PT' . $time . 'S'));
        $itemFound = $this->findBy(['cupid' => $cupid, 'racerid' => $racerid])
            ->where('start_time <= ? AND finish_time >= ?', $finishTime, $startTime)->fetch();
        return is_null($itemFound);
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
        return $this->findBy($filter)->order('time_seconds ASC');
    }
}