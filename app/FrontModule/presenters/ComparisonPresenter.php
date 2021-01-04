<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace FrontModule;


use App\Cups;
use App\CupsRoutes;
use App\Followers;
use App\ResultsRacers;

class ComparisonPresenter extends BaseSignPresenter
{
    private ResultsRacers $resultsRacers;
    private Followers $followers;
    private int $userid;
    private int $racerid;
    private Cups $cups;
    private CupsRoutes $cupsRoutes;

    public function __construct(ResultsRacers $resultsRacers, Followers $followers, Cups $cups, CupsRoutes $cupsRoutes)
    {
        $this->resultsRacers = $resultsRacers;
        $this->followers = $followers;
        $this->cups = $cups;
        $this->cupsRoutes = $cupsRoutes;
    }

    public function startup()
    {
        parent::startup();
        $this->userid = (int)$this->user->getId();
        $this->racerid = $this->cups->getRacerid($this->cups->getActive(), $this->userid);
    }

    public function actionDefault()
    {
        $followersList = $this->followers->whoFollows($this->racerid);
        if (!is_null($followersList)) {
            $followingList = [];
            foreach ($followersList as $following) {
                $followingList[(int)$following->follow_racerid] = (int)$following->follow_racerid;
            }
            if (sizeof($followingList) > 0) {
                $followingResults = $this->resultsRacers->findAll()->where('categoryid IS NULL AND resultid.racerid IN ?', $followingList)->fetchAll();
                $followersResults = [];
                foreach ($followingResults as $followerResult) {
                    $races[$followerResult->ref('resultid')->raceid] = $followerResult->ref('resultid')->raceid;
                    $followersResults[$followerResult->ref('resultid')->racerid][$followerResult->ref('resultid')->raceid][] = $followerResult;
                }
            }
        }
        $myResults = $this->resultsRacers->findAll()->where('categoryid IS NULL AND resultid.racerid = ?', $this->racerid);
        $races = [];
        $followersResults = [];
        foreach ($myResults as $myResult) {
            $races[$myResult->ref('resultid')->raceid] = $myResult->ref('resultid')->raceid;
            $followersResults[$this->racerid][$myResult->ref('resultid')->raceid][] = $myResult;
        }
        $this->template->races = $this->cupsRoutes->findBy(['cups_routes.id' => $races])->select('cups_routes.id, legend_name, routeid.description');
        $this->template->results = $followersResults;
        $this->template->followersList = $followersList;
        $this->template->racerid = $this->racerid;

    }
}