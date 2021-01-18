<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


interface StartControlFactory
{
    public function create(int $cupid, callable $onStart, callable $onStop): StartControl;
}