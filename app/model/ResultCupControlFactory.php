<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;

interface ResultCupControlFactory
{
    public function create(int $cupid): ResultCupControl;
}