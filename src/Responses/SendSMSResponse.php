<?php

namespace Omalizadeh\SMS\Responses;

readonly class SendSMSResponse
{
    public function __construct(private int $messageId, private ?float $cost = null) {}

    /**
     * Get sms provider message id.
     *
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * Get cost of sent sms.
     *
     * @return ?float
     */
    public function getCost(): ?float
    {
        return $this->cost;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message_id' => $this->getMessageId(),
            'cost' => $this->getCost(),
        ];
    }
}
