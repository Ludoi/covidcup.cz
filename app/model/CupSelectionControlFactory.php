<?php
declare(strict_types=1);
/*
   Copyright (C) 2021 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


interface CupSelectionControlFactory
{
    public function create(int $cupid, callable $onSelect): CupSelectionControl;
}