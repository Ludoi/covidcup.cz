<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


class ResultsCalculation
{
    private Cups $cups;
    private Results $results;
    private int $cupid;

    public function __construct(Cups $cups, Results $results)
    {
        $this->cups = $cups;
        $this->results = $results;
        $this->cupid = $this->cups->getActive();
    }

    public function calculate(): void
    {

    }

}