<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


interface iResultCalc
{
    public function setResults(Results $results): void;

    public function calculate(): array;

    public function setCups(int $cupid, Cups $cups): void;

    public function getRaceResults(int $raceid): array;
}