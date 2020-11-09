<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use Bunny\Message;
use Contributte\RabbitMQ\Consumer\IConsumer;
use DateTime;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use ZipArchive;

final class GPXConsumer implements IConsumer
{
    private Measurements $measurements;
    private Cups $cups;
    private Results $results;
    private Messages $messages;
    private Cache $cache;

    public function __construct(Measurements $measurements, Cups $cups, Results $results, Messages $messages,
                                IStorage $storage)
    {
        $this->measurements = $measurements;
        $this->cups = $cups;
        $this->results = $results;
        $this->messages = $messages;
        $this->cache = new Cache($storage);
    }

    public function consume(Message $message): int
    {
        $messageCreator = 'gpx';
        $messageData = json_decode($message->content);
        $directory = APP_DIR . '/../files/gpx/results/';

        $headers = $message->headers;

        $cupid = (int)$messageData->cupid;
        $racerid = (int)$messageData->racerid;
        $filename = $directory . $messageData->filename;
        if (!file_exists($filename)) {
            // file was not found
            $this->messages->insertMessage($racerid, 'GPX soubor nebyl nalezen. Výsledek nebyl uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }
        $zipFilename = $messageData->filename . '.zip';
        $wholeZipFilename = $directory . $zipFilename;

        $now = new DateTime();
        $gpx = new GPXParser($filename);

        $content = file_get_contents($filename);
        $zipFile = new ZipArchive();
        if ($zipFile->open($wholeZipFilename, ZipArchive::CREATE) !== true) {
        } else {
            $zipFile->addFromString($messageData->filename, $content);
            $zipFile->close();
            unlink($filename);
        }

        $fileHash = hash('md5', $content);
        if (!is_null($this->measurements->findOneBy(['file_hash' => $fileHash]))) {
            // file was already used
            $this->messages->insertMessage($racerid, 'GPX soubor už byl použit. Výsledek nebyl znovu uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }

        $startPoint = $gpx->getStartPoint();
        $finishPoint = $gpx->getFinishPoint();
        $startTime = $gpx->getStartTime();
        $finishTime = $gpx->getFinishTime();

        if (is_null($startTime)) {
            // start time not found
            $this->messages->insertMessage($racerid, 'Čas startu nebyl nalezen. Výsledek nebyl uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }

        if (is_null($finishTime)) {
            // finish time not found
            $this->messages->insertMessage($racerid, 'Cílový čas nebyl nalezen. Výsledek nebyl uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }

        $duration = (int)$finishTime->format('U') - (int)$startTime->format('U');
        if ($duration < 0) {
            // time is wrong
            $this->messages->insertMessage($racerid, 'Celkový čas je záporný. Výsledek nebyl uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }

        if (!$this->cups->isDateValid($cupid, $startTime, true)) {
            // date from file is invalid
            $this->messages->insertMessage($racerid, 'Čas startu je mimo dobu konání poháru. Výsledek nebyl uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }

        $correct = $this->results->isItemCorrect($cupid, (int)$messageData->raceid,
            (int)$messageData->racerid, $startTime, $duration);
        if (!$correct) {
            // such start time is already stored
            $this->messages->insertMessage($racerid, 'Výsledek s podobným časem startu už byl uložen. Tento výsledek nebyl uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }

        $guaranteed = true;
        $startDistance = $this->cups->getDistance($cupid, (int)$messageData->raceid,
            $startPoint['latitude'], $startPoint['longitude'], true);
        if (is_null($startDistance) || $startDistance > 0.2) {
            // start point uncertain
            $this->messages->insertMessage($racerid, 'Místo startu neodpovídá trase. Výsledek byl uložen, ale neověřený.',
                'warning', $messageCreator);
            $guaranteed = false;
        }

        $finishDistance = $this->cups->getDistance($cupid, (int)$messageData->raceid,
            $finishPoint['latitude'], $finishPoint['longitude'], false);
        if (is_null($finishDistance) || $finishDistance > 0.2) {
            // finish point uncertain
            $this->messages->insertMessage($racerid, 'Místo cíle neodpovídá trase. Výsledek byl uložen, ale neověřený.',
                'warning', $messageCreator);
            $guaranteed = false;
        }

        $measurement = $this->measurements->insertGPXDetails((int)$messageData->racerid, (int)$messageData->raceid,
            $startTime, $startPoint['latitude'], $startPoint['longitude'], $startDistance, $finishTime,
            $finishPoint['latitude'], $finishPoint['longitude'], $finishDistance, $zipFilename, $fileHash);
        if (!is_null($measurement)) {
            $this->results->insertItem($cupid, (int)$measurement->raceid, $racerid, $measurement->start_time, $duration,
                $guaranteed, (int)$measurement->id);
            $this->cleanCache((int)$measurement->raceid);
        }

        return IConsumer::MESSAGE_ACK; // Or ::MESSAGE_NACK || ::MESSAGE_REJECT
    }

    private function cleanCache(int $raceid): void
    {
        $this->cache->clean([Cache::TAGS => ['resultEnter', "resultEnter_$raceid", "resultOrder_$raceid"]]);
    }

}