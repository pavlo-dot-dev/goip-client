<?php

namespace PavloDotDev\GoipClient\Entities\SimBank;

readonly class Info
{
    public function __construct(
        public string $serialNumber,
        public string $firmwareVersion,
        public string $hardwareModel,
        public string $currentTime,
    ) {
    }
}
