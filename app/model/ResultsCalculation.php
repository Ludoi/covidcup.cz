<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Json;

class ResultsCalculation
{
    private Cups $cups;
    private Results $results;
    private int $cupid;
    private ResultsOverall $resultsOverall;
    private Cache $cache;

    public function __construct(Cups $cups, Results $results, ResultsOverall $resultsOverall, IStorage $storage)
    {
        $this->cups = $cups;
        $this->results = $results;
        $this->cupid = $this->cups->getActive();
        $this->resultsOverall = $resultsOverall;
        $this->cache = new Cache($storage);
    }

    public function calculate(int $cupid): void
    {
        $cup = $this->cups->find($cupid);
        $className = (string)$cup->calc_class;
        $calculation = new $className();
        $calculation->setCups($cupid, $this->cups);
        $calculation->setResults($this->results);
        $results = $calculation->calculate();
        $this->resultsOverall->insert(['cupid' => $cupid, 'created' => new \DateTime(), 'content' => Json::encode($results)]);
        $this->cache->clean(['tags' => ["overallResults_$cupid"]]);
    }

}