<?php

namespace Omalizadeh\SMS\Drivers\Kavenegar;

use Omalizadeh\SMS\Responses\SendSMSResponse;

readonly class Entry
{
    public function __construct(
        private int $messageid,
        private string $message,
        private int $status,
        private string $statustext,
        private string $sender,
        private string $receptor,
        private int $date,
        private int $cost,
    ) {}

    public static function fromArray(array $entry): static
    {
        return new static(
            $entry['messageid'],
            $entry['message'],
            $entry['status'],
            $entry['statustext'],
            $entry['sender'],
            $entry['receptor'],
            $entry['date'],
            $entry['cost'],
        );
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function toSendSMSResponse(): SendSMSResponse
    {
        return new SendSMSResponse($this->messageid, $this->cost);
    }
}
