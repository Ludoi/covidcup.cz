<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


class Cups extends Table
{
    protected ?string $tableName = 'cups';

    public function getActive(): int
    {
        return 2;
    }

    public function getRacerid(int $cupid, int $userid): ?int
    {
        $racer = $this->find($cupid)->related('cups_racers')->where('userid = ?', $userid)->fetch();
        if (!is_null($racer)) {
            return (int)$racer->id;
        } else {
            return null;
        }
    }

    public function getRaceid(int $cupid, int $routeid): ?int
    {
        $race = $this->find($cupid)->related('cups_routes')->where('routeid = ?', $routeid)->fetch();
        if (!is_null($race)) {
            return (int)$race->id;
        } else {
            return null;
        }
    }

    public function isDateValid(int $cupid, \DateTime $dateTime, bool $untilToday): bool
    {
        $result = false;
        $cup = $this->find($cupid);
        if (!is_null($cup)) {
            $from = $cup->valid_from->format('U');
            $to = $cup->valid_to->format('U');
            $now = (new \DateTime())->format('U');
            $dateToCheck = $dateTime->format('U');
            $to = $untilToday ? $now : $to;
            if ($dateToCheck >= $from && $dateToCheck <= $to) $result = true;
        }
        return $result;
    }

    public function getDistance(int $cupid, int $raceid, ?float $latitude, ?float $longitude, bool $start): ?float
    {
        $race = $this->find($cupid)->related('cups_routes')->where('id = ?', $raceid)->fetch()->ref('routeid');
        if (!is_null($race)) {
            if ($start) {
                $pointLatitude = (float)$race->ref('point_from')->latitude;
                $pointLongitude = (float)$race->ref('point_from')->longitude;
            } else {
                $pointLatitude = (float)$race->ref('point_to')->latitude;
                $pointLongitude = (float)$race->ref('point_to')->longitude;
            }
            return ResultUtil::distance($pointLatitude, $pointLongitude, $latitude, $longitude);
        } else {
            return null;
        }

    }
}