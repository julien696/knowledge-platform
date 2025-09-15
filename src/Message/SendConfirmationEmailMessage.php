<?php

namespace App\Message;

class SendConfirmationEmailMessage
{
    public function __construct(
        private int $userId
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }
}
