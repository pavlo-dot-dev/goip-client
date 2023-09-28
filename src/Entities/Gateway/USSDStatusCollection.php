<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

readonly class USSDStatusCollection
{
    /** @var USSDStatus[] */
    public array $collection;

    public function __construct(array $linesData)
    {
        $this->collection = array_map(fn(array $item) => new USSDStatus(
            line: $item['line'],
            id: $item['smskey'],
            status: $item['status'],
            message: $item['error']
        ), $linesData);
    }

    /**
     * @return USSDStatus[]
     */
    public function filterById(string $id): array
    {
        return array_values(
            array_filter($this->collection, fn(USSDStatus $item) => $item->id === $id)
        );
    }
}
