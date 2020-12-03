<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Database\Table\Selection;

class Followers extends Table
{
    protected ?string $tableName = 'followers';

    public function whoIsFollowing(int $racerid): Selection
    {
        return $this->findBy(['follow_racerid' => $racerid]);
    }

    public function whoFollows(int $racerid): Selection
    {
        return $this->findBy(['racerid' => $racerid]);
    }

    public function insertItem(int $racerid, int $followRacerid): void
    {
        $now = new \DateTime();
        $this->insert(['racerid' => $racerid, 'follow_racerid' => $followRacerid, 'created' => $now]);
    }

    public function removeFollower(int $racerid, int $followRacerid): void
    {
        $this->findOneBy(['racerid' => $racerid, 'follow_racerid' => $followRacerid])->delete();
    }

    public function isFollowing(int $racerid, int $followRacerid): bool
    {
        $result = $this->findOneBy(['racerid' => $racerid, 'follow_racerid' => $followRacerid]);
        return !is_null($result);
    }
}