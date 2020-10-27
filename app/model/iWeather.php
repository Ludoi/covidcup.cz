<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


interface iWeather
{
    public function setPointid(int $pointid): void;
    public function getPointid(): int;
    public function getWeather(Weather $weather): void;
}