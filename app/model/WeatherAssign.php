<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


class WeatherAssign
{
    private Weather $weather;
    private Cups $cups;
    private Results $results;
    private Routes $routes;
    private CupsRoutes $races;

    public function __construct(Weather $weather, Cups $cups, Results $results, Routes $routes, CupsRoutes $races)
    {
        $this->weather = $weather;
        $this->cups = $cups;
        $this->results = $results;
        $this->routes = $routes;
        $this->races = $races;
    }

    public function assign()
    {
        $routes = $this->routes->findBy(['point_to' => 10]);
        $races = $this->races->findBy(['cupid' => $this->cups->getActive(), 'routeid' => $routes]);
        $results = $this->results->findBy(['routeid' => $races, 'weatherid' => null]);
        $oneHour = new \DateInterval('PT1H');
        foreach ($results as $result) {
            $measureTimeMax = clone $result->start_time;
            $measureTimeMax->add($oneHour);
            $weathers = $this->weather->findBy(['pointid' => 10])
                ->where('measure_time >= ? AND measure_time <= ?', $result->start_time,
                    $measureTimeMax)->fetchAll();
            foreach ($weathers as $weather) {
                $result->update(['weatherid' => $weather->id]);
                break;
            }
        }
    }
}