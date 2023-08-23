<?php

namespace PavloDotDev\GoipClient\Entities;

readonly class GoipItem
{
    public function __construct(
        public string|int $id,
        public string|int $termId,
        public string|int $provider,
        public string $address,
        public string|int $port,
        public string|int|null $imsi = null,
        public string|int|null $imei = null,
    ) {
    }
}
