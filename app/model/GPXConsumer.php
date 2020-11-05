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
use ZipArchive;

final class GPXConsumer implements IConsumer
{
    private Measurements $measurements;
    private Cups $cups;
    private Results $results;
    private Messages $messages;

    public function __construct(Measurements $measurements, Cups $cups, Results $results, Messages $messages)
    {
        $this->measurements = $measurements;
        $this->cups = $cups;
        $this->results = $results;
        $this->messages = $messages;
    }

    public function consume(Message $message): int
    {
        $messageCreator = 'gpx';
        $messageData = json_decode($message->content);
        $directory = APP_DIR . '/../files/gpx/results/';

        $headers = $message->headers;

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

        if (!$this->cups->isDateValid($this->cups->getActive(), $startTime, true)) {
            // date from file is invalid
            $this->messages->insertMessage($racerid, 'Čas startu je mimo dobu konání poháru. Výsledek nebyl uložen.',
                'danger', $messageCreator);
            return IConsumer::MESSAGE_ACK;
        }

        $guaranteed = true;
        $startDistance = $this->cups->getDistance($this->cups->getActive(), (int)$messageData->raceid,
            $startPoint['latitude'], $startPoint['longitude'], true);
        if (is_null($startDistance) || $startDistance > 0.2) {
            // start point uncertain
            $this->messages->insertMessage($racerid, 'Místo startu neodpovídá trase. Výsledek byl uložen, ale neověřený.',
                'warning', $messageCreator);
            $guaranteed = false;
        }

        $finishDistance = $this->cups->getDistance($this->cups->getActive(), (int)$messageData->raceid,
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
            $duration = (int)$measurement->finish_time->format('U') - (int)$measurement->start_time->format('U');
            $this->results->insert(['cupid' => $this->cups->getActive(), 'raceid' => $measurement->raceid,
                'racerid' => (int)$messageData->racerid, 'start_time' => $measurement->start_time, 'time_seconds' => $duration,
                'created' => $now, 'active' => true, 'guaranteed' => $guaranteed, 'measurementid' => $measurement->id]);
        }

        return IConsumer::MESSAGE_ACK; // Or ::MESSAGE_NACK || ::MESSAGE_REJECT
    }

}