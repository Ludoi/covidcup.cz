<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Database\Table\ActiveRow;

class ResultsOverall extends Table
{
    protected ?string $tableName = 'results_overall';

    public function getLastSlice(int $cupid): ?ActiveRow
    {
        return $this->findBy(['cupid' => $cupid])->order('created DESC')->limit(1)->fetch();
    }

    public function getSlice(int $id): ?ActiveRow
    {
        return $this->find($id);
    }

    public function getAllSlices(int $cupid): array
    {
        return $this->findBy(['cupid' => $cupid])->order('created DESC')->fetchPairs('id', 'created');
    }
}