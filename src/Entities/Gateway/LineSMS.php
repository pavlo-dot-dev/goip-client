<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

/**
 * @property SMS[] $sms
 */
readonly class LineSMS
{
    public function __construct(
        public int $line,
        public array $sms,
    ) {
    }
}
