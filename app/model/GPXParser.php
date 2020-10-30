<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use DateTime;
use DOMDocument;

class GPXParser
{
    private object $gpx;
    private bool $valid;
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->valid = $this->isValid();
        if ($this->valid) $this->gpx = simplexml_load_file($filename);
    }

    private function isValid(): bool
    {
        libxml_use_internal_errors(true);

        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->load($this->filename);

        $errors = libxml_get_errors();

        if (empty($errors)) {
            return true;
        }

        $error = $errors[0];
        if ($error->level < 3) {
            return true;
        }
        return false;
    }

    private function checkGpx(): bool
    {
        if ($this->isValid()) {
            return true;
        } else {
            return false;
        }
    }

    public function getStartPoint(): array {
        $point = [];
        if ($this->checkGpx()) {
            $point['latitude'] = (float)$this->gpx->trk->trkseg->trkpt[0]['lat'];
            $point['longitude'] = (float)$this->gpx->trk->trkseg->trkpt[0]['lon'];
        }
        return $point;
    }

    public function getFinishPoint(): array {
        $point = [];
        if ($this->checkGpx()) {
            $count = sizeof($this->gpx->trk->trkseg->trkpt);
            $point['latitude'] = (float)$this->gpx->trk->trkseg->trkpt[$count - 1]['lat'];
            $point['longitude'] = (float)$this->gpx->trk->trkseg->trkpt[$count - 1]['lon'];
        }
        return $point;
    }

    public function getStartTime(): ?DateTime
    {
        if ($this->checkGpx()) {
            $timeStr = (string)$this->gpx->trk->trkseg->trkpt[0]->time;
            if ($timeStr != '') {
                $time = new DateTime($timeStr);
                return $time;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getFinishTime(): ?DateTime
    {
        if ($this->checkGpx()) {
            $count = sizeof($this->gpx->trk->trkseg->trkpt);
            $timeStr = (string)$this->gpx->trk->trkseg->trkpt[$count - 1]->time;
            if ($timeStr != '') {
                $time = new DateTime($timeStr);
                return $time;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}