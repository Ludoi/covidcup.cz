<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;

use Contributte\RabbitMQ\Producer\Producer;

final class GPXQueue
{

    /**
     * @var Producer
     */
    private Producer $gpxProducer;


    public function __construct(Producer $gpxProducer)
    {
        $this->gpxProducer = $gpxProducer;
    }


    public function publish(int $racerid, int $routeid, string $filename): void
    {
        $json = json_encode(['racerid' => $racerid, 'routeid' => $routeid, 'filename' => $filename]);
        $headers = [];

        $this->gpxProducer->publish($json, $headers);
    }

}