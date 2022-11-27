<?php

namespace Omalizadeh\Sms;

class SentSmsInfo
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
}
