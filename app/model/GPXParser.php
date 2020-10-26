<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


class GPXParser
{
    private object $gpx;

    public function __construct(string $filename)
    {
        $this->gpx = simplexml_load_file($filename);
    }

    private function checkGpx(): bool {
        return sizeof($this->gpx->xpath('/trk/trkseg/trkpt')) > 0;

    }

    public function getStartPoint(): array {
        $point = [];
        if ($this->checkGpx()) {
            $point['latitude'] = $this->gpx->trk->trkseg->trkpt[0]->lat;
            $point['longitude'] = $this->gpx->trk->trkseg->trkpt[0]->lon;
        }
        return $point;
    }

    public function getFinishPoint(): array {
        $point = [];
        if ($this->checkGpx()) {
            $count = sizeof($this->gpx->trk->trkseg->trkpt);
            $point['latitude'] = $this->gpx->trk->trkseg->trkpt[$count - 1]->lat;
            $point['longitude'] = $this->gpx->trk->trkseg->trkpt[$count - 1]->lon;
        }
        return $point;
    }

    public function getStartTime(): ?int {
        if ($this->checkGpx()) {
            $time = new \DateTime($this->gpx->trk->trkseg->trkpt[0]->time);
            return (int)$time->format('U');
        } else {
            return null;
        }
    }

    public function getFinishTime(): ?int {
        if ($this->checkGpx()) {
            $count = sizeof($this->gpx->trk->trkseg->trkpt);
            $time = new \DateTime($this->gpx->trk->trkseg->trkpt[$count - 1]->time);
            return (int)$time->format('U');
        } else {
            return null;
        }
    }
}