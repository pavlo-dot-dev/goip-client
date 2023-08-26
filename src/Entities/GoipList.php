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
        $simNumber = explode("\n", explode('SIM Number:', $simInfo)[1] ?? '')[0] ?: null;
        $imsi = explode("\n", explode('IMSI:', $simInfo)[1] ?? '')[0] ?: null;
        $imei = explode("\n", explode('IMEI:', $simInfo)[1] ?? '')[0] ?: null;
        $carrier = explode("\n", explode('Carrier:', $simInfo)[1] ?? '')[0] ?: null;

        return new GoipItem(
            id: $data['c'],
            termId: $data['id'],
            provider: $data['provider'],
            ip: explode(':', $data['ip:port'])[0],
            port: explode(':', $data['ip:port'])[1],
            password: $data['passwd'],
            gsmLogin: $data['gsm_login'] === 'LOGIN',
            voipLogin: $data['voip_login'] === 'LOGIN',
            simNumber: $simNumber,
            imsi: $imsi,
            imei: $imei,
            carrier: $carrier,
        );
    }
}
