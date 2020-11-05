<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App\Console;


use App\Cups;
use App\ResultsCalculation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResultsCommand extends Command
{
    protected static $defaultName = 'results:calculate';
    private ResultsCalculation $resultsCalculation;
    private Cups $cups;

    public function __construct(ResultsCalculation $resultsCalculation, Cups $cups)
    {
        parent::__construct();
        $this->resultsCalculation = $resultsCalculation;
        $this->cups = $cups;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->resultsCalculation->calculate($this->cups->getActive());
        return 0;
    }
}