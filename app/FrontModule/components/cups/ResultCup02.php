<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Application\UI\ITemplate;
use Nette\Utils\Json;

class ResultCup02
{
    private Cups $cups;
    private ResultsOverall $resultsOverall;
    private CupsRacers $cupsRacers;
    private Categories $categories;
    private CupsRoutes $cupsRoutes;
    private ?int $slice = null;
    private ?int $catid = null;
    private int $cupid;

    public function __construct(Cups $cups, ResultsOverall $resultsOverall, CupsRacers $cupsRacers, Categories $categories, CupsRoutes $cupsRoutes, int $cupid)
    {
        $this->cups = $cups;
        $this->resultsOverall = $resultsOverall;
        $this->cupsRacers = $cupsRacers;
        $this->categories = $categories;
        $this->cupsRoutes = $cupsRoutes;
        $this->cupid = $cupid;
    }

    public function prepareResults(ITemplate $template, int $slice, int $catid): void
    {
        if (is_null($slice)) {
            $resultsOverall = $this->resultsOverall->getLastSlice($this->cupid);
        } else {
            $resultsOverall = $this->resultsOverall->getSlice($slice);
        }
        if (is_null($resultsOverall)) {
            return;
        }
        $template->slices = $this->resultsOverall->getAllSlices($this->cupid);
        $template->results = Json::decode((string)$resultsOverall->content, Json::FORCE_ARRAY);
        $template->created = $resultsOverall->created;
        $max = 0;
        $racersList = [];
        $categoriesList = [];
        foreach ($template->results['racers'] as $id => $racer) {
            $categoriesList[$racer['category']] = $racer['category'];
            if (!is_null($catid) && $catid != 0) {
                if ($racer['category'] != $catid) {
                    unset($template->results['racers'][$id]);
                }
            }
        }

        foreach ($template->results['racers'] as $racer) {
            $racersList[] = $racer['racerid'];
            if ($racer['result_count'] > $max) $max = $racer['result_count'];
        }
        $template->max = ($max > 9 ? 9 : $max);
        $template->racers = [];
        foreach ($this->cupsRacers->findBy(['id' => $racersList]) as $racer) {
            $template->racers[$racer->id] = $racer->ref('userid');
        }
        $template->categories = [];
        foreach ($this->categories->findBy(['id' => $categoriesList]) as $category) {
            $template->categories[$category->id] = $category;
        }
        $template->categories[0]['catid'] = 'dokupy';
        $racesList = [];
        foreach ($template->results['races'] as $race) {
            $racesList[$race['raceid']] = $race['raceid'];
        }
        $template->races = [];
        foreach ($this->cupsRoutes->findBy(['id' => $racesList]) as $race) {
            $template->races[$race->id]['legend_name'] = $race->legend_name;
            $template->races[$race->id]['description'] = $race->ref('routeid')->description;
        }
    }

    public function render()
    {
        $this->prepareResults();
        $template->render(__DIR__ . '/resultCup02.latte');
    }
}