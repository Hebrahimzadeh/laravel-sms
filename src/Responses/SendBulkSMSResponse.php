<?php

namespace Omalizadeh\SMS\Responses;

readonly class SendBulkSMSResponse
{
    public function __construct(
        /** @param array<int, SendSMSResponse> $records */
        private array $records,
        private ?string $bulkId = null,
        private ?float $totalCost = null,
    ) {}

    /**
     * Get array containing all sent sms records.
     *
     * @return array<int, SendSMSResponse>
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * Get sms provider bulk id.
     *
     * @return null|string
     */
    public function getBulkId(): ?string
    {
        return $this->bulkId;
    }

    /**
     * Get total cost of sending bulk sms.
     *
     * @return ?float
     */
    public function getTotalCost(): ?float
    {
        return $this->totalCost;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'records' => array_map(fn(SendSMSResponse $record) => $record->toArray(), $this->getRecords()),
            'total_cost' => $this->getTotalCost(),
        ];
    }
}
