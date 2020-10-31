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

    public function getUnreadMessages(int $userid): Selection
    {
        return $this->findBy(['userid' => $userid, 'displayed' => false]);
    }

    public function resetUnreadMessages(int $userid): void
    {
        $this->findBy(['userid' => $userid, 'displayed' => false])->update(['displayed' => true]);
    }

    public function clearReadMessages(): void
    {
        $this->findBy(['displayed' => true])->delete();
    }

    public function insertMessage(int $userid, string $message, string $type, string $createdBy): void
    {
        $now = new \DateTime();
        $this->insert(['userid' => $userid, 'message' => $message, 'type' => $type, 'created' => $now,
            'displayed' => false, 'created_by' => $createdBy]);
    }
}