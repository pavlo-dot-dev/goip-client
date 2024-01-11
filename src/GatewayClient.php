<?php

namespace PavloDotDev\GoipClient;

use PavloDotDev\GoipClient\Abstract\BasicClient;
use PavloDotDev\GoipClient\Entities\Gateway\IMEICollection;
use PavloDotDev\GoipClient\Entities\Gateway\Info;
use PavloDotDev\GoipClient\Entities\Gateway\LineInfo;
use PavloDotDev\GoipClient\Entities\Gateway\LineSMS;
use PavloDotDev\GoipClient\Entities\Gateway\LineSMSCollection;
use PavloDotDev\GoipClient\Entities\Gateway\OnlineInfo;
use PavloDotDev\GoipClient\Entities\Gateway\OnlineLine;
use PavloDotDev\GoipClient\Entities\Gateway\SMS;
use PavloDotDev\GoipClient\Entities\Gateway\USSDStatus;
use PavloDotDev\GoipClient\Entities\Gateway\USSDStatusCollection;
use Symfony\Component\DomCrawler\Crawler;

class GatewayClient extends BasicClient
{
    public function online(): OnlineInfo
    {
        $crawler = $this->request('/default/en_US/status.xml?type=list&ajaxcachebust='.(time() * 1000));

        $rawData = [];

        $crawler
            ->filter('status > *')
            ->each(function (Crawler $crawler) use (&$rawData) {
                $name = $crawler->nodeName();
                $value = $crawler->innerText();
                $line = preg_replace("/[^0-9]/", "", explode('_', $name)[0]);
                $nameWithoutLine = implode('_', array_slice(explode('_', $name), 1));

                if (!isset($rawData[$line])) {
                    $rawData[$line] = [
                        'line' => $line,
                    ];
                }

                $rawData[$line][$nameWithoutLine] = $value;
            });

        $lines = [];
        foreach ($rawData as $item) {
            $lines[] = new OnlineLine(
                line: $item['line'],
                state: $item['line_state'] ?? null,
            );
        }

        return new OnlineInfo($lines);
    }

    public function info(): Info
    {
        $crawler = $this->request('/default/en_US/status.html?type=gsm');

        $data = $crawler
            ->filter('table')
            ->eq(2)
            ->filter('tr')
            ->each(fn(Crawler $crawler) => $crawler->filter('td')->each(fn(Crawler $crawler) => $crawler->text()));

        if (mb_strpos($data[0][0] ?? null, 'Serial Number') === false) {
            throw new \Exception('Error parsing Serial Number');
        }
        if (mb_strpos($data[1][0] ?? null, 'Firmware Version') === false) {
            throw new \Exception('Error parsing Firmware Version');
        }
        if (mb_strpos($data[2][0] ?? null, 'Module Version') === false) {
            throw new \Exception('Error parsing Module Version');
        }
        if (mb_strpos($data[5][0] ?? null, 'Current Time') === false) {
            throw new \Exception('Error parsing Current Time');
        }

        $gsmInfo = self::parseTable($crawler, '#gsm_info > table tr');
        $gsmDetail = self::parseTable($crawler, '#gsm_detail > table tr');

        $lines = [];
        foreach ($gsmInfo as $i => $item) {
            $detail = $gsmDetail[$i];

            $lines[] = new LineInfo(
                line: intval($item['CH']),
                gsm: $item['GSM'] === 'Y',
                carrier: $item['Carrier'] ?: null,
                simNumber: $detail['SIM Number'] ?: null,
                imei: $detail['IMEI'] ?: null,
                imsi: $detail['IMSI'] ?: null,
                iccid: $detail['ICCID'] ?: null,
            );
        }

        return new Info(
            serialNumber: $data[0][1],
            firmwareVersion: $data[1][1],
            moduleVersion: $data[2][1],
            currentTime: $data[5][1],
            lines: $lines
        );
    }

    public function imei(): IMEICollection
    {
        $crawler = $this->request('/default/en_US/config.html?type=imei');

        $tableData = $crawler
            ->filter('#imei_settings > table tr')
            ->each(function (Crawler $crawler) {
                $columns = $crawler->filter('td');

                $line = self::parseLine($columns->eq(0)->text());
                $imei = $crawler->filter('input')->attr('value');

                if ($line <= 0) {
                    return null;
                }

                return compact('line', 'imei');
            });
        $tableData = array_filter($tableData);

        return new IMEICollection($tableData);
    }

    public function sms(string $phone, string $content, array $lines = null)
    {
        $crawler = $this->request('/default/en_US/tools.html?type=sms');
        $id = $crawler->filter('input[name="smskey"]')->attr('value');
        $allLines = array_filter(
            $crawler->filter('input[type="checkbox"]')->each(
                fn(Crawler $crawler) => self::parseLine($crawler->attr('name'))
            )
        );

        $post = [
            'smskey' => $id,
            'action' => 'SMS',
            'telnum' => $phone,
            'smscontent' => $content,
            'send' => 'Send',
            'all' => $lines === null,
        ];
        foreach ($allLines as $line) {
            $post['line'.$line] = $lines === null || in_array($line, $lines);
        }

        $this->request('/default/en_US/sms_info.html?type=sms', null, $post);

        return $id;
    }

    public function smsStatus()
    {
    }

    public function ussd(string $code, array $lines = null): string
    {
        $crawler = $this->request('/default/en_US/ussd_info.html?type=ussd');
        $id = $crawler->filter('input[name="smskey"]')->attr('value');
        $allLines = array_filter(
            $crawler->filter('input[type="checkbox"]')->each(
                fn(Crawler $crawler) => self::parseLine($crawler->attr('name'))
            )
        );

        $post = [
            'smskey' => $id,
            'action' => 'USSD',
            'telnum' => $code,
            'send' => 'Send',
            'all' => $lines === null,
        ];
        foreach ($allLines as $line) {
            $post['line'.$line] = $lines === null || in_array($line, $lines);
        }

        $this->request('/default/en_US/ussd_info.html?type=ussd', null, $post);

        return $id;
    }

    /**
     * @param  string|null  $id
     * @return USSDStatus[]
     */
    public function ussdStatus(): USSDStatusCollection
    {
        $crawler = $this->request('/default/en_US/send_sms_status.xml', [
            'line' => '',
            'ajaxcachebust' => time(),
        ]);

        $lines = [];
        $crawler->filter('send-sms-status > *')->each(function (Crawler $crawler) use (&$lines) {
            $line = self::parseLine($crawler->nodeName());
            if (!isset($lines[$line])) {
                $lines[$line] = compact('line');
            }
            $lines[$line][str_replace($line, '', $crawler->nodeName())] = $crawler->text();
        });
        $lines = array_values($lines);

        return new USSDStatusCollection($lines);
    }

    public function smsOutboxTruncate(): void
    {
        $this->request('/default/en_US/tools.html', [
            'type' => 'sms_outbox',
            'action' => 'del',
            'line' => -1,
            'pos' => -1,
        ]);
    }

    public function smsInboxTruncate(): void
    {
        $this->request('/default/en_US/tools.html', [
            'type' => 'sms_inbox',
            'action' => 'del',
            'line' => -1,
            'pos' => -1,
        ]);
    }

    public function smsInbox(): LineSMSCollection
    {
        $linesData = [];

        $crawler = $this->request('/default/en_US/tools.html?type=sms_inbox');
        $content = $crawler
            ->filter('script')
            ->last()
            ->text();
        $content = html_entity_decode($content);
        $content = array_slice(explode("sms=", $content), 1);
        foreach ($content as $i => $item) {
            $item = trim(
                explode("];", $item)[0].']'
            );
            $array = json_decode($item, true);
            $array = array_map('trim', $array);
            $array = array_values(
                array_filter($array)
            );

            $linesData[] = [
                'line' => $i + 1,
                'sms' => array_map(
                    function (string $item) {
                        list($date, $sender, $message) = explode(',', $item, 3);

                        return new SMS(
                            date: $date,
                            sender: $sender,
                            message: $message
                        );
                    },
                    $array
                ),
            ];
        }

        return new LineSMSCollection(
            array_map(fn(array $item) => new LineSMS($item['line'], $item['sms']), $linesData)
        );
    }

    public function smsOutbox(): LineSMSCollection
    {
        $linesData = [];

        $crawler = $this->request('/default/en_US/tools.html?type=sms_outbox');
        $content = $crawler
            ->filter('script')
            ->last()
            ->text();
        $content = html_entity_decode($content);
        $content = array_slice(explode("sms=", $content), 1);
        foreach ($content as $i => $item) {
            $item = trim(
                explode("];", $item)[0].']'
            );
            $array = json_decode($item, true);
            $array = array_map('trim', $array);
            $array = array_values(
                array_filter($array)
            );

            $linesData[] = [
                'line' => $i + 1,
                'sms' => array_map(
                    function (string $item) {
                        list($date, $sender, $message) = explode(',', $item, 3);

                        return new SMS(
                            date: $date,
                            sender: $sender,
                            message: $message
                        );
                    },
                    $array
                ),
            ];
        }

        return new LineSMSCollection(
            array_map(fn(array $item) => new LineSMS($item['line'], $item['sms']), $linesData)
        );
    }

    protected static function parseTable(Crawler $crawler, string $rowFilterPath): array
    {
        $tableData = $crawler
            ->filter($rowFilterPath)
            ->each(
                fn(Crawler $crawler) => $crawler->filter('td')->each(
                    fn(Crawler $crawler) => trim($crawler->text(), " \n\r\t\v\x00\xc2\xa0")
                )
            );
        $tableData = array_values(
            array_filter(
                $tableData,
                fn(array $item) => count($item) > 1,
            )
        );
        $tableHeaders = $tableData[0];
        $tableData = array_values(
            array_filter(
                $tableData,
                fn(array $item) => intval($item[0]) > 0 && count($item) === count($tableHeaders),
            )
        );
        return array_map(fn(array $item) => array_combine($tableHeaders, $item), $tableData);
    }

    protected static function parseLine(string $string = null): int
    {
        return intval(preg_replace("/[^0-9]/", "", $string));
    }
}
