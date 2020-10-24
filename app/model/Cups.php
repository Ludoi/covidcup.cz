<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Tracy\Dumper;

class Cups extends Table
{
    protected ?string $tableName = 'cups';

    public function getActive(): int
    {
        return 1;
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
}