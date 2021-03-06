<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


interface ResultEnterControlFactory
{
    public function create(int $cupid, ?int $raceid, array $onInsert): ResultEnterControl;
}