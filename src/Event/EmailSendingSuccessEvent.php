<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class EmailSendingSuccessEvent extends Event
{
    public function __construct(private string $successMessage) {}

    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }
}
