<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

readonly class LineInfo
{
    public function __construct(
        public int $line,
        public bool $gsm,
        public ?string $carrier,
        public ?string $simNumber,
        public ?string $imei,
        public ?string $imsi,
        public ?string $iccid
    ) {
    }
}
