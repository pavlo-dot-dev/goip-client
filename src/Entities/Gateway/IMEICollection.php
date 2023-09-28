<?php

namespace PavloDotDev\GoipClient\Entities\Gateway;

readonly class IMEICollection
{
    public array $collection;

    public function __construct(array $tableData)
    {
        $this->collection = array_map(fn(array $item) => new LineIMEI($item['line'], $item['imei']), $tableData);
    }
}
