<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Database\Table\Selection;

class Messages extends Table
{
    protected ?string $tableName = 'messages';

    public function getUnreadMessages(int $racerid): Selection
    {
        return $this->findBy(['racerid' => $racerid, 'displayed' => false]);
    }

    public function resetUnreadMessages(int $racerid): void
    {
        $this->findBy(['racerid' => $racerid, 'displayed' => false])->update(['displayed' => true]);
    }

    public function clearReadMessages(): void
    {
        $this->findBy(['displayed' => true])->delete();
    }

    public function insertMessage(int $racerid, string $message, string $type, string $createdBy): void
    {
        $now = new \DateTime();
        $this->insert(['racerid' => $racerid, 'message' => $message, 'type' => $type, 'created' => $now,
            'displayed' => false, 'created_by' => $createdBy]);
    }
}