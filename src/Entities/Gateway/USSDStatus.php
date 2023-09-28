<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

readonly class USSDStatus
{
    public function __construct(
        public int $line,
        public string $id,
        public string $status,
        public ?string $message,
    ) {
    }
}
