<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Application\UI\Control;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class ResultCupControl extends Control
{
    private Cups $cups;
    private ResultsOverall $resultsOverall;
    private CupsRacers $cupsRacers;
    private Categories $categories;
    private CupsRoutes $cupsRoutes;
    private ?int $sliceid = null;
    private ?int $catid = null;
    private int $cupid;
    private Cache $cache;

    public function __construct(Cups $cups, ResultsOverall $resultsOverall, CupsRacers $cupsRacers,
                                Categories $categories, CupsRoutes $cupsRoutes, IStorage $storage, int $cupid)
    {
        $this->cups = $cups;
        $this->resultsOverall = $resultsOverall;
        $this->cupsRacers = $cupsRacers;
        $this->categories = $categories;
        $this->cupsRoutes = $cupsRoutes;
        $this->cupid = $cupid;
        $this->cache = new Cache($storage);
    }

    public function handleChangeView(int $slice, int $catid)
    {
        $this->sliceid = $slice;
        $this->catid = $catid;
    }

    public function render()
    {
        if (is_null($this->sliceid)) {
            $slice = $this->resultsOverall->getLastSlice($this->cupid);
            if (!is_null($slice)) {
                $this->sliceid = (int)$slice->id;
            } else {
                $this->flashMessage('Zatím bez výsledků', 'info');
                return;
            }
        }
        if (is_null($this->catid)) {
            $this->catid = 0;
        }
        $this->template->cacheid = "overallResults_{$this->cupid}_{$this->sliceid}_{$this->catid}";
        $this->template->tags = ['overallResults', "overallResults_{$this->cupid}",
            "overallResults_{$this->cupid}_{$this->sliceid}_{$this->catid}"];
        $cacheItem = $this->cache->load($this->template->cacheid);
        if (is_null($cacheItem)) {
            $result = new ResultCup02($this->cups, $this->resultsOverall, $this->cupsRacers, $this->categories,
                $this->cupsRoutes, $this->cupid);
            $this->template->catidSelected = $this->catid;
            $this->template->sliceidSelected = $this->sliceid;
            $result->prepareResults($this->template, $this->sliceid, $this->catid);
        }

        $this->template->resultCupInclude = __DIR__ . '/cups/resultCup02.latte';
        $this->template->render(__DIR__ . '/resultCup.latte');
    }
}