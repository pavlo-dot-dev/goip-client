<?php

namespace PavloDotDev\GoipClient\Entities;

readonly class InboxSMSItem
{
    public function __construct(
        public int $id,
        public string $time,
        public string $smscNumber,
        public string $sourceNumber,
        public string $termId,
        public string $text,
    ) {
    }
}
