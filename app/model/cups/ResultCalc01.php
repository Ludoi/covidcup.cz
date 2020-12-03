<?php
declare(strict_types=1);

/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/

namespace App;


class ResultCalc01 implements iResultCalc
{
    private Results $results;
    private Cups $cups;
    private int $cupid;

    public function setResults(Results $results): void
    {
        $this->results = $results;
    }

    public function calculate(): array
    {
        $cup = $this->cups->find($this->cupid);
        $racesList = $cup->related('cups_routes')->fetchAll();
        $results = $this->results->findBy(['raceid' => $racesList]);
        // get best times and number of races per racers
        $races = [];
        $racers = [];
        foreach ($results as $result) {
            if (!isset($races[$result->raceid])) {
                $races[$result->raceid] = ['racerid' => $result->racerid, 'time_seconds' => $result->time_seconds,
                    'max_time' => 2.5 * $result->time_seconds, 'count' => 0, 'raceid' => $result->raceid];
            } elseif ($races[$result->raceid]['time_seconds'] > $result->time_seconds) {
                $races[$result->raceid]['racerid'] = $result->racerid;
                $races[$result->raceid]['time_seconds'] = $result->time_seconds;
                $races[$result->raceid]['max_time'] = 2.5 * $result->time_seconds;
            }
            $races[$result->raceid]['count']++;
            if (!isset($racers[$result->racerid])) {
                $categories = $result->ref('racerid')->related('racers_categories')->fetchAll();
                $catid = null;
                foreach ($categories as $category) {
                    $catid = (int)$category->catid;
                }
                $racers[$result->racerid] = ['racerid' => $result->racerid, 'result_count' => 0, 'races' => [],
                    'races_unique' => 0, 'points' => 0,
                    'overall_points' => 0, 'category' => $catid];
            }
            $racers[$result->racerid]['result_count']++;
            $racers[$result->racerid]['races'][] = ['raceid' => $result->raceid,
                'time_seconds' => $result->time_seconds, 'points' => 0, 'resultid' => $result->id];
        }

        foreach ($racers as &$racer) {
            foreach ($racer['races'] as &$race) {
                if ($race['time_seconds'] < $races[$race['raceid']]['max_time']) {
                    $delta = $races[$race['raceid']]['max_time'] - $races[$race['raceid']]['time_seconds'];
                    $race['points'] = round((1 - ($race['time_seconds'] - $races[$race['raceid']]['time_seconds']) / $delta) * 50, 1);
                } else {
                    $race['points'] = 0;
                }
            }
        }

        foreach ($racers as &$racer) {
            ResultUtil::sort($racer['races'], ['points' => SORT_DESC]);
            $uniqueRoute = [];
            $points = 0;
            $pointsAll = 0;
            $counter = 0;
            foreach ($racer['races'] as &$race) {
                $counter++;
                $uniqueRoute[$race['raceid']] = 1;
                if ($counter <= 8) $points += $race['points'];
                $pointsAll += $race['points'];
            }
            $racer['races_unique'] = sizeof($uniqueRoute);
            $racer['points'] = $points;
            $racer['overall_points'] = ($racer['races_unique'] > 8 ? 8 : $racer['races_unique']) * 10 + $points;
        }

        ResultUtil::sort($racers, ['overall_points' => SORT_DESC]);

        $overview['racers'] = $racers;
        $overview['races'] = $races;

        return $overview;
    }

    public function setCups(int $cupid, Cups $cups): void
    {
        $this->cupid = $cupid;
        $this->cups = $cups;
    }
}