<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


class Measurements extends Table
{
    protected ?string $tableName = 'measurements';

    public function isActive(int $userid): bool
    {
        return !is_null($this->findOneBy(['userid' => $userid, 'active' => true]));
    }

    public function insertStart(int $userid, int $routeid, \DateTime $startTime, ?float $startLatitude, ?float $startLongitude): void
    {
        if (!$this->isActive($userid)) {
            $this->insert(['userid' => $userid, 'routeid' => $routeid, 'start_time' => $startTime,
                'start_latitude' => $startLatitude, 'start_longitude' => $startLongitude, 'active' => true]);
        }
    }

    public function updateFinish(int $userid, \DateTime $finishTime, ?float $finishLatitude, ?float $finishLongitude): ?int
    {
        if ($this->isActive($userid)) {
            $measurement = $this->findOneBy(['userid' => $userid, 'active' => true]);
            $measurement->update(['finish_time' => $finishTime,
                'finish_latitude' => $finishLatitude, 'finish_longitude' => $finishLongitude, 'active' => false]);
            return (int)$measurement->id;
        } else {
            return null;
        }
    }

    public function deactivate(): void
    {
        $activeMeasurements = $this->findBy(['active' => true])->fetchAll();
        $now = new \DateTime();
        foreach ($activeMeasurements as $measurement) {
            $distance = (int)ResultUtil::subtractTimes($now, $measurement->start_time)->format('U');
            if ($distance > 5 * 60 * 60) {
                $measurement->update(['active' => false]);
            }
        }
    }
}