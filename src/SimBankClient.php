<?php

namespace PavloDotDev\GoipClient;

use PavloDotDev\GoipClient\Abstract\Client;
use PavloDotDev\GoipClient\Entities\SimBank\Info;
use Symfony\Component\DomCrawler\Crawler;

class SimBankClient extends Client
{
    protected ?string $login = null;
    protected ?string $password = null;

    public function request(string $path, array $get = null, array $post = null): Crawler
    {
        if ($get) {
            $path .= '?'.http_build_query($get);
        }

        $this->response = $this->client->request($post ? 'POST' : 'GET', $path, [
            'form_params' => $post,
            'auth' => [$this->login, $this->password]
        ]);

        $this->content = $this->response->getBody()->getContents();

        return new Crawler($this->content, $this->baseUri);
    }

    public function auth(string $login, string $password): void
    {
        $this->login = $login;
        $this->password = $password;
        $this->authorized = false;

        try {
            $this->request('default/en_US/status.html');
            $this->authorized = true;
        } catch (\Exception $e) {
            if (mb_strpos($e->getMessage(), 'Please Authenticate') !== false) {
                throw new \Exception('Error authorization');
            }

            throw $e;
        }
    }

    public function info(): Info
    {
        $data = $this->request('default/en_US/status.html')
            ->filter('#smb_info')
            ->first()
            ->filter('tr')
            ->each(fn(Crawler $item) => $item->filter('td')->each(fn(Crawler $item) => $item->html()));

        return new Info(
            serialNumber: $data[0][1],
            firmwareVersion: $data[1][1],
            hardwareModel: $data[2][1],
            currentTime: $data[3][1],
        );
    }

    public function simCards(): array
    {
        $data = [];

        $this->request('default/en_US/power_status.xml')
            ->filter('status > *')
            ->each(function (Crawler $item) use (&$data) {
                if (preg_match('/^check(\d+)$/', $item->nodeName(), $matches)) {
                    $slot = intval($matches[1]);
                    $power = !!$item->html();

                    $data[] = compact('slot', 'power');
                }
            });

        return $data;
    }

    public function changePowerSim(int $slot, bool $power): void
    {
        $formData = $this->request('default/en_US/power.html')
            ->filter('#power_from')
            ->form()
            ->getValues();
        if ($power) {
            $formData['s'.$slot.'_p'] = 'on';
        } elseif (isset($formData['s'.$slot.'_p'])) {
            unset($formData['s'.$slot.'_p']);
        }

        $this->request('default/en_US/power.html', null, $formData);
    }
}
