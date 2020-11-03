<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App\Console;


use App\WeatherAssign;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WeatherAssignCommand extends Command
{
    protected static $defaultName = 'weather:assign';
    /**
     * @var WeatherAssign
     */
    private WeatherAssign $weatherAssign;

    public function __construct(WeatherAssign $weatherAssign)
    {
        parent::__construct();
        $this->weatherAssign = $weatherAssign;
    }

    protected function configure(): void
    {
        $this->setName('weather:assign');
        $this->setDescription('Assigns the weather data to race results');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->weatherAssign->assign();
        return 0;
    }

}