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
    private ResultsOverall $resultsOverall;
    private Cache $cache;
    private ResultsRacers $resultsRacers;

    public function __construct(Cups $cups, Results $results, ResultsOverall $resultsOverall, IStorage $storage,
                                ResultsRacers $resultsRacers)
    {
        $this->cups = $cups;
        $this->results = $results;
        $this->resultsOverall = $resultsOverall;
        $this->cache = new Cache($storage);
        $this->resultsRacers = $resultsRacers;
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
        $cupRoutes = $cup->related('cups_routes')->fetchAll();
        foreach ($cupRoutes as $cupRoute) {
            $raceResults = $calculation->getRaceResults($cupRoute->id);
            foreach ($raceResults['overall_results'] as $racerResult) {
                $resultDb = $this->resultsRacers->findOneBy(['resultid' => $racerResult['resultid'], ['categoryid' => null]]);
                if (is_null($resultDb)) {
                    $this->resultsRacers->insert(['resultid' => $racerResult['resultid'], 'categoryid' => null,
                        'pos' => $racerResult['pos'], 'points' => $racerResult['points']]);
                } else {
                    $resultDb->update(['pos' => $racerResult['pos'], 'points' => $racerResult['points']]);
                }
            }
            foreach ($raceResults['category_results'] as $categoryid => $categoryResult) {
                foreach ($categoryResult as $racerResult) {
                    $resultDb = $this->resultsRacers->findOneBy(['resultid' => $racerResult['resultid'], ['categoryid' => $categoryid]]);
                    if (is_null($resultDb)) {
                        $this->resultsRacers->insert(['resultid' => $racerResult['resultid'], 'categoryid' => $categoryid,
                            'pos' => $racerResult['pos'], 'points' => $racerResult['points']]);
                    } else {
                        $resultDb->update(['pos' => $racerResult['pos'], 'points' => $racerResult['points']]);
                    }
                }
            }
            $this->cache->clean(['tags' => ["resultOrder_{$cupRoute->id}"]]);
        }
        $this->cache->clean(['tags' => ["overallResults_$cupid"]]);
    }
}