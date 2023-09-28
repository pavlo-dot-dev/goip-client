<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

readonly class SMS
{
    public function __construct(
        public string $date,
        public string $sender,
        public string $message,
    ) {
    }
}
