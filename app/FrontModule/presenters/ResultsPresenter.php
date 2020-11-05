<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Categories;
use App\Cups;
use App\CupsRacers;
use App\CupsRoutes;
use App\ResultsOverall;
use Nette\Utils\Json;

class ResultsPresenter extends BaseSignPresenter
{
    private Cups $cups;
    private ResultsOverall $resultsOverall;
    private CupsRacers $cupsRacers;
    private Categories $categories;
    private CupsRoutes $cupsRoutes;

    public function __construct(Cups $cups, ResultsOverall $resultsOverall, CupsRacers $cupsRacers,
                                Categories $categories, CupsRoutes $cupsRoutes)
    {
        $this->cups = $cups;
        $this->resultsOverall = $resultsOverall;
        $this->cupsRacers = $cupsRacers;
        $this->categories = $categories;
        $this->cupsRoutes = $cupsRoutes;
    }

    public function actionDefault(?int $cat = null, ?int $id)
    {
        $cupid = $this->cups->getActive();

        if (is_null($id)) {
            $resultsOverall = $this->resultsOverall->getLastSlice($cupid);
        } else {
            $resultsOverall = $this->resultsOverall->getSlice($id);
        }
        if (is_null($resultsOverall)) {
            $this->flashMessage('Výsledky nejsou k dispozici.', 'warning');
            $this->redirect('Homepage:');
        }
        $this->template->slices = $this->resultsOverall->getAllSlices($cupid);
        $this->template->results = Json::decode((string)$resultsOverall->content, Json::FORCE_ARRAY);
        $this->template->created = $resultsOverall->created;
        $this->template->cat = $cat;
        $max = 0;
        $racersList = [];
        $categoriesList = [];
        foreach ($this->template->results['racers'] as $id => $racer) {
            $categoriesList[$racer['category']] = $racer['category'];
            if (!is_null($cat) && $cat != 0) {
                if ($racer['category'] != $cat) {
                    unset($this->template->results['racers'][$id]);
                }
            }
        }

        foreach ($this->template->results['racers'] as $racer) {
            $racersList[] = $racer['racerid'];
            if ($racer['result_count'] > $max) $max = $racer['result_count'];
        }
        $this->template->max = ($max > 8 ? 8 : $max);
        $this->template->racers = [];
        foreach ($this->cupsRacers->findBy(['id' => $racersList]) as $racer) {
            $this->template->racers[$racer->id] = $racer->ref('userid');
        }
        $this->template->categories = [];
        foreach ($this->categories->findBy(['id' => $categoriesList]) as $category) {
            $this->template->categories[$category->id] = $category;
        }
        $this->template->categories[0]['catid'] = 'dokupy';
        $racesList = [];
        foreach ($this->template->results['races'] as $race) {
            $racesList[$race['raceid']] = $race['raceid'];
        }
        $this->template->races = [];
        foreach ($this->cupsRoutes->findBy(['id' => $racesList]) as $race) {
            $this->template->races[$race->id]['legend_name'] = $race->legend_name;
            $this->template->races[$race->id]['description'] = $race->ref('routeid')->description;
        }
##        Dumper::dump($this->template->results); die;
    }
}