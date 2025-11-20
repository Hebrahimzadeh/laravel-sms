<?php

namespace Omalizadeh\SMS\Requests;

class SendBulkSMSRequest
{
    public function __construct(
        private readonly array $phoneNumbers,
        private readonly string $message,
        private ?string $sender = null,
    ) {}

    public function getPhoneNumbers(): array
    {
        return $this->phoneNumbers;
    }

    public function getMessage(): string
    {
        return $this->message;
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
