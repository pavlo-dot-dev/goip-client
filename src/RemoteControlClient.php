<?php

namespace PavloDotDev\GoipClient;


use GuzzleHttp\Client;
use PavloDotDev\GoipClient\Abstract\BasicClient;
use PavloDotDev\GoipClient\Entities\RemoteControl\Info;
use PavloDotDev\GoipClient\Entities\RemoteControl\InfoItem;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

class RemoteControlClient extends BasicClient
{
    public function info(): Info
    {
        $crawler = $this->request('/');

        $parseVersion = explode('v', $crawler->filter('tr a')->eq(0)->innerText());
        $parseVersion = explode(' ', $parseVersion[1]);
        $parseVersion = $parseVersion[0];

        $dataArray = $crawler->filter('tr')->each(
            fn(Crawler $row) => $row->filter('td')->each(
                fn(Crawler $column) => $column->filter('a')->count() > 0 ? $column->filter('a')->attr(
                    'href'
                ) : $column->text()
            )
        );
        $dataArray = array_values(
            array_filter($dataArray, fn(array $item) => count($item) === 4)
        );

        return new Info(
            $parseVersion,
            array_map(
                fn(array $item) => new InfoItem($item[0], $item[1], $item[2]),
                $dataArray
            )
        );
    }
}
