<?php

namespace Omalizadeh\SMS\Requests;

class SendSMSRequest
{
    public function __construct(
        private readonly string $phoneNumber,
        private readonly string $message,
        private ?string $sender = null,
    ) {}

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getMessage(): string
    {
        return trim($this->message);
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): static
    {
        $this->sender = $sender;

        return $this;
    }
}
