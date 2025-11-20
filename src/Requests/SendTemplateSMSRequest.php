<?php

namespace Omalizadeh\SMS\Requests;

class SendTemplateSMSRequest
{
    public function __construct(
        private readonly string $phoneNumber,
        private readonly int|string $template,
        /** @param <int|string, int|string> $parameters */
        private readonly array $parameters,
        private ?string $sender = null,
    ) {}

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getTemplate(): int|string
    {
        return $this->template;
    }

    public function getParameters(): array
    {
        return $this->parameters;
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
