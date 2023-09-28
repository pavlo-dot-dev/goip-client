<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

/**
 * @property LineInfo[] $lines
 */
readonly class Info
{
    public function __construct(
        public string $serialNumber,
        public string $firmwareVersion,
        public string $moduleVersion,
        public string $currentTime,
        public array $lines,
    ) {
    }
}
