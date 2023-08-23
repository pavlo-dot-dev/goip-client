<?php

namespace PavloDotDev\GoipClient;


use PavloDotDev\GoipClient\Abstract\Client;
use PavloDotDev\GoipClient\Entities\GoipList;
use PavloDotDev\GoipClient\Entities\InboxSMS;
use PavloDotDev\GoipClient\Entities\USSDList;

class GoipClient extends Client
{
    public function goipList(): GoipList
    {
        return new GoipList($this, 'en/goip.php');
    }

    public function ussdList(): USSDList
    {
        return new USSDList($this, 'en/ussdinfo.php');
    }

    public function ussd(string|int $termId, string $code, bool $disconnect = false): string
    {
        if ($disconnect) {
            $this->ussdDisconnect($termId);
        }

        $crawler = $this->request(
            'en/ussd.php',
            [
                'debug' => 1,
                'TERMID' => $termId,
            ]
        );

        $form = $crawler->filter('form')->form();
        $formValues = $form->getValues();
        $formValues['Submit'] = 'Send';
        $formValues['USSDMSG'] = $code;

        $crawler = $this->request($form->getUri(), null, $formValues);

        $text = $crawler->filter('td.tdbg')->eq(1)->text();

        if (mb_strpos($text, 'GSM no response') !== false) {
            throw new \Exception($text);
        }

        return $text;
    }

    public function ussdDisconnect(string|int $termId): void
    {
        $this->request('en/ussd.php', [
            'TERMID' => $termId,
            'debug' => 1,
            'USSDMSG' => 1,
            'action' => 'exit'
        ]);
    }

    public function inboxSMS(string|int $termId = null): InboxSMS
    {
        $get = null;
        $post = null;

        if ($termId) {
            $get = [
                'action' => 'search'
            ];
            $post = [
                'column' => 'goipname',
                'type' => 'equal',
                's_key' => $termId,
            ];
        }

        return new InboxSMS(
            api: $this,
            path: 'en/receive.php',
            get: $get,
            post: $post,
        );
    }
}
