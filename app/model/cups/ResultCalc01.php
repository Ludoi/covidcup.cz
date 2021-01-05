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
    private array $racers;
    private array $races;
    const FACTOR = 2.5;
    const MAX_POINTS = 50;
    const COUNTABLE = 8;

    public function setResults(Results $results): void
    {
        $this->results = $results;
    }

    public function calculate(): array
    {
        $cup = $this->cups->find($this->cupid);
        $cupRoutes = $cup->related('cups_routes')->fetchAll();
        foreach ($cupRoutes as $cupRoute) $this->calculateRace((int)$cupRoute->id);

        $races = [];
        $racers = [];

        foreach ($this->races as $raceid => $race) {
            $races[$raceid] = ['racerid' => $race['racerid'], 'time_seconds' => $race['bestTime'],
                'count' => $race['count'], 'raceid' => $raceid, 'results' => []];
            foreach ($race['overall_results'] as $racerResult) {
                if (!isset($racers[$racerResult['racerid']])) {
                    $racers[$racerResult['racerid']] = ['racerid' => $racerResult['racerid'], 'result_count' => 0, 'races' => [],
                        'races_unique' => 0, 'points' => 0,
                        'overall_points' => 0, 'category' => $this->racers[$racerResult['racerid']]['category']];
                }
                $racers[$racerResult['racerid']]['result_count']++;
                $racers[$racerResult['racerid']]['races'][] = ['raceid' => $raceid,
                    'time_seconds' => $racerResult['time_seconds'], 'points' => $racerResult['points'],
                    'resultid' => $racerResult['resultid']];
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
                if ($counter <= self::COUNTABLE) $points += $race['points'];
                $pointsAll += $race['points'];
            }
            $racer['races_unique'] = sizeof($uniqueRoute);
            $racer['points'] = $points;
            $racer['overall_points'] = ($racer['races_unique'] > self::COUNTABLE ? self::COUNTABLE : $racer['races_unique']) * 10 + $points;
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

    private function getPoints(int $bestTime, int $time): float
    {
        if ($bestTime == $time) {
            $points = self::MAX_POINTS;
        } elseif ($bestTime !== 0) {
            $points = self::MAX_POINTS * (self::FACTOR * $bestTime - $time) / ((self::FACTOR - 1) * $bestTime);
            if ($points < 0) $points = 0;
        } else {
            $points = 0;
        }
        return round($points, 1);
    }

    private function calculateRace(int $raceid): void
    {
        $results = $this->results->findBy(['raceid' => $raceid]);
        // get best times and number of races per racers
        $racers = [];
        foreach ($results as $result) {
            if (!isset($race)) {
                $race = ['racerid' => (int)$result->racerid, 'bestTime' => (int)$result->time_seconds,
                    'raceid' => (int)$result->raceid, 'overall_results' => [], 'category_results' => [], 'count' => 0];
            } elseif ($race['bestTime'] > $result->time_seconds) {
                $race['racerid'] = (int)$result->racerid;
                $race['bestTime'] = (int)$result->time_seconds;
            }
            $race['count']++;
            if (!isset($this->racers[$result->racerid])) {
                $categories = $result->ref('racerid')->related('racers_categories')->fetchAll();
                $catid = null;
                foreach ($categories as $category) {
                    $catid = (int)$category->catid;
                }
                $this->racers[$result->racerid] = ['racerid' => $result->racerid, 'result_count' => 0,
                    'category' => $catid];
            }
            $race['overall_results'][] = ['racerid' => $result->racerid, 'time_seconds' => $result->time_seconds,
                'delta' => 0, 'points' => 0, 'resultid' => $result->id, 'pos' => 0];
            $race['category_results'][$this->racers[$result->racerid]['category']][] =
                ['racerid' => $result->racerid, 'time_seconds' => $result->time_seconds, 'delta' => 0, 'points' => 0,
                    'resultid' => $result->id, 'pos' => 0];
            $racers[$result->racerid]['races'][] = ['raceid' => $result->raceid,
                'time_seconds' => $result->time_seconds, 'points' => 0, 'resultid' => $result->id];
        }

        ResultUtil::countPos($race['overall_results'], ['time_seconds' => SORT_ASC], 'pos');
        foreach ($race['category_results'] as &$categoryResult) {
            ResultUtil::countPos($categoryResult, ['time_seconds' => SORT_ASC], 'pos');
        }

        foreach ($race['overall_results'] as &$overallResultRacer) {
            $overallResultRacer['delta'] = $overallResultRacer['time_seconds'] - $race['bestTime'];
            $overallResultRacer['points'] = $this->getPoints($race['bestTime'], $overallResultRacer['time_seconds']);
        }

        foreach ($race['category_results'] as &$categoryResult) {
            $categoryBestTime = 0;
            foreach ($categoryResult as &$categoryResultRacer) {
                if ($categoryBestTime == 0) $categoryBestTime = $categoryResultRacer['time_seconds'];
                $categoryResultRacer['delta'] = $categoryResultRacer['time_seconds'] - $categoryBestTime;
                $categoryResultRacer['points'] = $this->getPoints($race['bestTime'], $categoryResultRacer['time_seconds']);
            }
        }

        $this->races[$raceid] = $race;
    }

    public function getRaceResults(int $raceid): array
    {
        if (!isset($this->races[$raceid])) $this->calculateRace($raceid);
        return $this->races[$raceid];
    }
}