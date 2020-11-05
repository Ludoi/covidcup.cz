<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App\Console;


use App\Categories;
use App\Cups;
use App\CupsRacers;
use App\RacersCategories;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResultsCategoriesCommand extends Command
{
    protected static $defaultName = 'results:categories';
    private RacersCategories $racersCategories;
    private CupsRacers $cupsRacers;
    private Categories $categories;
    private Cups $cups;

    public function __construct(RacersCategories $racersCategories, CupsRacers $cupsRacers, Categories $categories,
                                Cups $cups)
    {
        parent::__construct();
        $this->racersCategories = $racersCategories;
        $this->cupsRacers = $cupsRacers;
        $this->categories = $categories;
        $this->cups = $cups;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $db = $this->cups->getDatabase();
        $cupid = $this->cups->getActive();
        $cup = $this->cups->find($cupid);
        $year = $cup->valid_from->format('Y');
        $racers = $this->cupsRacers->findBy(['cupid' => $cupid]);
        $db->beginTransaction();
        $this->racersCategories->findBy(['racerid' => $cup->related('cups_racers')->fetchAll()])->delete();
        foreach ($racers as $racer) {
            $age = $year - $racer->ref('userid')->year;
            $category = $this->categories->getCategory($cupid, (string)$racer->ref('userid')->gender, $age);
            if (!is_null($category)) $this->racersCategories->insert(['racerid' => $racer->id, 'catid' => $category]);
        }
        $db->commit();
        return 0;
    }
}