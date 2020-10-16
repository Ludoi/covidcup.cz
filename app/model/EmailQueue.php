<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;

use Contributte\RabbitMQ\Producer\Producer;

final class EmailQueue
{

    /**
     * @var Producer
     */
    private Producer $emailProducer;


    public function __construct(Producer $emailProducer)
    {
        $this->emailProducer = $emailProducer;
    }


    public function publish(array $emailContent): void
    {
        $json = json_encode($emailContent);
        $headers = [];

        $this->emailProducer->publish($json, $headers);
    }

}