<?php

namespace Omalizadeh\Sms;

use Illuminate\Contracts\Support\Arrayable;

class SentSmsInfo implements Arrayable
{
    private int $messageId;
    private float $cost;

    public function __construct(int $messageId, float $cost)
    {
        $this->messageId = $messageId;
        $this->cost = $cost;
    }

    /**
     * get sms provider message id
     *
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * @return float
     */
    public function getCost(): float
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
