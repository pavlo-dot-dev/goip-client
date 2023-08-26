<?php

namespace PavloDotDev\GoipClient\Entities;

readonly class GoipItem
{
    public function __construct(
        public string|int $id,
        public string|int $termId,
        public string|int $provider,
        public string $ip,
        public string|int $port,
        public string|null $password,
        public bool $gsmLogin,
        public bool $voipLogin,
        public string|null $simNumber,
        public string|int|null $imsi,
        public string|int|null $imei,
        public string|null $carrier,
    ) {
    }
}
