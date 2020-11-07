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


    public function publish(int $cupid, int $racerid, int $raceid, string $filename): void
    {
        $json = json_encode(['cupid' => $cupid, 'racerid' => $racerid, 'raceid' => $raceid, 'filename' => $filename]);
        $headers = [];

        $this->gpxProducer->publish($json, $headers);
    }

}