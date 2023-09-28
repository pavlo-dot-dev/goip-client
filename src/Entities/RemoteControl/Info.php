<?php

namespace PavloDotDev\GoipClient\Entities\RemoteControl;

/**
 * @property InfoItem[] $items
 */
readonly class Info
{
    public function __construct(
        public string $version,
        public array $items,
    ) {
    }
}
