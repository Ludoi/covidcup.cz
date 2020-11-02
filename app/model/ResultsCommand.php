<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App\Console;


use App\ResultsCalculation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResultsCommand extends Command
{
    protected static $defaultName = 'results:calculate';
    private ResultsCalculation $resultsCalculation;

    public function __construct(ResultsCalculation $resultsCalculation)
    {
        parent::__construct();
        $this->resultsCalculation = $resultsCalculation;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->resultsCalculation->calculate();
    }
}