<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

/**
 * @property OnlineLine[] $lines
 */
readonly class OnlineInfo
{
    public function __construct(
        public array $lines,
    ) {
    }
}
