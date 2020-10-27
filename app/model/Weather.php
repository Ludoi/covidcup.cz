<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use DOMDocument;
use Nette\Database\ResultSet;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class Weather extends Table
{
    protected ?string $tableName = 'weather';

    public function insertItem(int $pointid, DateTime $measureTime, float $temperature, float $humidity,
                               float $snow, float $wind, float $pressure, string $visibility, string $remark)
    {
        $this->insert(['pointid' => $pointid, 'measure_time' => $measureTime, 'temperature' => $temperature,
            'humidity' => $humidity, 'snow' => $snow, 'wind' => $wind, 'pressure' => $pressure,
            'visibility' => $visibility, 'remark' => $remark]);
    }

    public function getWeather(int $pointid, DateTime $when): ActiveRow
    {
        $this->findBy(['pointid' => $pointid])->order('measure_time DESC')->where('measure_time <= ?', $when)->fetch();
    }

    public function getPage(int $pointid, ?int $top = null, int $limit = 10): ResultSet
    {
        $this->findBy(['pointid' => $pointid])->order('measure_time DESC')->limit($limit, $top);
    }

}