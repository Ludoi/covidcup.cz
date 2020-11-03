<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App\Console;


use App\Weather;
use App\WeatherLysa;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WeatherDownloadCommand extends Command
{
    protected static $defaultName = 'weather:download';
    private Weather $weather;

    public function __construct(Weather $weather)
    {
        parent::__construct();
        $this->weather = $weather;
    }

    protected function configure(): void
    {
        $this->setName('weather:download');
        $this->setDescription('Reads the weather data from public sources');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $weather = new WeatherLysa();
        $weather->setPointid(10);
        $weather->getWeather($this->weather);
        return 0;
    }

}