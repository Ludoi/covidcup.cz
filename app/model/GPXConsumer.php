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

    public function __construct(Measurements $measurements, Cups $cups, Results $results)
    {
        $this->measurements = $measurements;
        $this->cups = $cups;
        $this->results = $results;
    }

    public function consume(Message $message): int
    {
        $messageData = json_decode($message->content);
        $directory = APP_DIR . '/../files/gpx/results/';

        $headers = $message->headers;

        $filename = $directory . $messageData->filename;
        $zipFilename = $messageData->filename . '.zip';
        $wholeZipFilename = $directory . $zipFilename;

        $now = new DateTime();
        $gpx = new GPXParser($filename);

        $fileHash = hash_file('md5', $filename);
        if (!is_null($this->measurements->findOneBy(['file_hash' => $fileHash]))) {
            // file was already used
            return IConsumer::MESSAGE_ACK;
        }

        $startPoint = $gpx->getStartPoint();
        $finishPoint = $gpx->getFinishPoint();
        $startTime = $gpx->getStartTime();
        $finishTime = $gpx->getFinishTime();

        if (!$this->cups->isDateValid($this->cups->getActive(), $startTime, true)) {
            // date from file is invalid
            return IConsumer::MESSAGE_ACK;
        }

        $measurement = $this->measurements->insertGPXDetails((int)$messageData->racerid, (int)$messageData->routeid,
            $startTime, $startPoint['latitude'], $startPoint['longitude'], $finishTime,
            $finishPoint['latitude'], $finishPoint['longitude'], $zipFilename, $fileHash);
        if (!is_null($measurement)) {
            $duration = (int)$measurement->finish_time->format('U') - (int)$measurement->start_time->format('U');
            $this->results->insert(['cupid' => $this->cups->getActive(), 'routeid' => $measurement->routeid,
                'userid' => (int)$messageData->racerid, 'start_time' => $measurement->start_time, 'time_seconds' => $duration,
                'created' => $now, 'active' => true, 'guaranteed' => true, 'measurementid' => $measurement->id]);
        }

        $zipFile = new ZipArchive();
        if ($zipFile->open($wholeZipFilename, ZipArchive::CREATE) !== true) {
        } else {
            $zipFile->addFile($filename);
            $zipFile->close();
            unlink($filename);
        }

        return IConsumer::MESSAGE_ACK; // Or ::MESSAGE_NACK || ::MESSAGE_REJECT
    }

}