<?php

namespace Omalizadeh\Sms;

class BulkSentSmsInfo
{
    private array $messageIds;
    private float $totalCost;

    public function __construct(array $messageIds, float $totalCost)
    {
        $this->messageIds = $messageIds;
        $this->totalCost = $totalCost;
    }

    /**
     * get sms provider message ids
     *
     * @return array
     */
    public function getMessageIds(): array
    {
        return $this->messageIds;
    }

    /**
     * @return float
     */
    public function getTotalCost(): float
    {
        return $this->totalCost;
    }
}
