<?php

namespace PavloDotDev\GoipClient\Entities;

use Symfony\Component\DomCrawler\Crawler;

class GoipList extends DataTable
{
    public function current(): GoipItem
    {
        $data = array_combine($this->columns, $this->items[$this->position]);

        /** @var Crawler $raw */
        $raw = $data['raw'];

        $simInfo = $raw->filter('td')->eq(10)->attr('title');
        $imsi = explode("\n", explode('IMSI:', $simInfo)[1])[0];
        $imei = explode("\n", explode('IMEI:', $simInfo)[1])[0];

        return new GoipItem(
            id: $data['c'],
            termId: $data['id'],
            provider: $data['provider'],
            address: explode(':', $data['ip:port'])[0],
            port: explode(':', $data['ip:port'])[1],
            imsi: $imsi,
            imei: $imei
        );
    }
}
