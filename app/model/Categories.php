<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


class Categories extends Table
{
    protected ?string $tableName = 'categories';

    public function getCategory(int $cupid, string $gender, int $age): ?int
    {
        $categories = $this->findBy(['cupid' => $cupid, 'gender' => $gender])
            ->where('age_from <= ? AND age_to >= ?', $age, $age);
        foreach ($categories as $category) {
            return $category->id;
        }
        return null;
    }
}