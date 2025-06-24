<?php

namespace App\Message;

class UpdateBoosterStackMessage
{
    public function __construct(
        private readonly string $userId
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
} 