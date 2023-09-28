<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

/**
 * @property LineSMS[] $collection
 */
readonly class LineSMSCollection
{
    public function __construct(
        public array $collection
    ) {
    }

    public function firstByLine(int $line): ?LineSMS
    {
        foreach ($this->collection as $item) {
            if ($item->line === $line) {
                return $item;
            }
        }

        return null;
    }
}
