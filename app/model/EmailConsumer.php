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
use Nette\Mail\IMailer;

final class EmailConsumer implements IConsumer
{
    /** @var IMailer */
    private IMailer $mailer;

    private string $addressFrom = 'info@lysacup.cz';

    public function __construct(IMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function consume(Message $message): int
    {
        $messageData = json_decode($message->content);

        $headers = $message->headers;

        $mail = new \Nette\Mail\Message();
        $mail->setFrom($this->addressFrom)
            ->setHtmlBody($messageData->content);
        $to = explode(';', $messageData->to);
        foreach ($to as $address) {
            if ($address <> '')
                $mail->addTo($address);
        }
        $cc = explode(';', $messageData->cc);
        foreach ($cc as $address) {
            if ($address <> '')
                $mail->addCc($address);
        }
        $bcc = explode(';', $messageData->bcc);
        foreach ($bcc as $address) {
            if ($address <> '')
                $mail->addBcc($address);
        }
        $this->mailer->send($mail);

        return IConsumer::MESSAGE_ACK; // Or ::MESSAGE_NACK || ::MESSAGE_REJECT
    }

}