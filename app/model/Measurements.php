<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Database\Table\ActiveRow;

class Measurements extends Table
{
    protected ?string $tableName = 'measurements';

    public function isActive(int $userid): bool
    {
        return !is_null($this->findOneBy(['userid' => $userid, 'active' => true]));
    }

    public function insertStart(int $userid, int $routeid, \DateTime $startTime, ?float $startLatitude,
                                ?float $startLongitude, ?float $startDistance): void
    {
        if (!$this->isActive($userid)) {
            $this->insert(['userid' => $userid, 'routeid' => $routeid, 'start_time' => $startTime,
                'start_latitude' => $startLatitude, 'start_longitude' => $startLongitude,
                'start_distance' => $startDistance, 'active' => true]);
        }
    }

    public function updateFinish(int $userid, \DateTime $finishTime, ?float $finishLatitude,
                                 ?float $finishLongitude, ?float $finishDistance): ?int
    {
        if ($this->isActive($userid)) {
            $measurement = $this->findOneBy(['userid' => $userid, 'active' => true]);
            $measurement->update(['finish_time' => $finishTime,
                'finish_latitude' => $finishLatitude, 'finish_longitude' => $finishLongitude,
                'finish_distance' => $finishDistance, 'active' => false]);
            return (int)$measurement->id;
        } else {
            return null;
        }
    }

    public function insertGPXDetails(int $userid, int $routeid, \DateTime $startTime, ?float $startLatitude,
                                     ?float $startLongitude, ?float $startDistance, \DateTime $finishTime, ?float $finishLatitude,
                                     ?float $finishLongitude, ?float $finishDistance, string $gpxFile, string $fileHash): ActiveRow
    {
        return $this->insert(['userid' => $userid, 'routeid' => $routeid, 'start_time' => $startTime,
            'start_latitude' => $startLatitude, 'start_longitude' => $startLongitude,
            'start_distance' => $startDistance, 'finish_time' => $finishTime,
            'finish_latitude' => $finishLatitude, 'finish_longitude' => $finishLongitude,
            'finish_distance' => $finishDistance, 'gpx_file' => $gpxFile,
            'file_hash' => $fileHash, 'active' => false]);
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