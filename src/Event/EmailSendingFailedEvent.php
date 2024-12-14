<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class EmailSendingFailedEvent extends Event
{
    public function __construct(private string $errorMessage) {}

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
