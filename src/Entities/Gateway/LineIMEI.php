<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

readonly class LineIMEI
{
    public function __construct(
        public int $line,
        public string $imei
    ) {
    }
}
