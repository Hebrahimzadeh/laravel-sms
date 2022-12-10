<?php

namespace Omalizadeh\Sms;

use Illuminate\Contracts\Support\Arrayable;

class BulkSentSmsInfo implements Arrayable
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

    /**
     * @return int
     */
    public function getMessagesCount(): int
    {
        return count($this->messageIds);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'messages_count' => $this->getMessagesCount(),
            'total_cost' => $this->getTotalCost(),
            'message_ids' => $this->getMessageIds(),
        ];
    }
}
