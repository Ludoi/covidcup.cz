<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Database\Table\Selection;

class Plans extends Table
{
    protected ?string $tableName = 'plans';

    public function insertItem(int $cupid, int $raceid, int $racerid, string $comment, \DateTime $planDate)
    {
        $now = new \DateTime();
        $this->insert(['cupid' => $cupid, 'raceid' => $raceid, 'racerid' => $racerid, 'created' => $now,
            'comment' => $comment, 'plan_date' => $planDate, 'active' => true]);
    }

    public function getItems(int $cupid, ?bool $active, ?int $raceid, ?int $racerid): Selection
    {
        $now = new \DateTime();
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
        return $this->findBy($filter)->order('plan_date ASC')->where('plan_date > ?', $now);
    }
}