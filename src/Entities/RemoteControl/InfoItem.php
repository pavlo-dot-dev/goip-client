<?php

namespace PavloDotDev\GoipClient\Entities\RemoteControl;

readonly class InfoItem
{
    public function __construct(
        public string $name,
        public string $ip,
        public string $url,
    ) {
    }
}
