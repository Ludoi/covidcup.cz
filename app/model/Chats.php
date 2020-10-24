<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

class Chats extends Table
{
    protected ?string $tableName = 'chat';

    public function insertItem(int $cupid, int $userid, string $content, string $tcpip)
    {
        $now = new DateTime();
        $this->insert(['cupid' => $cupid, 'content' => $content, 'tcpip' => $tcpip, 'created' => $now, 'userid' => $userid, 'active' => true]);
    }

    public function getItems(int $cupid, ?bool $active): Selection
    {
        $filter = ['cupid' => $cupid];
        if (!is_null($active)) {
            $filter[] = ['active' => $active];
        }
        return $this->findBy($filter)->order('created DESC');
    }
}