# GoIP Parser Wrapper in PHP

# Требования

* PHP 8.0 или выше
* Composer
* GuzzleHTTP

# Composer

```bash
composer require pavlo-dot-dev/goip-client
```

# Примеры

### Авторизация

```php
$baseURI = 'http://.../goip';
$login = 'root';
$password = '...';

$client = new \PavloDotDev\GoipClient\GoipClient($baseURI, $login, $password);
```

### Получение списка GoIP

```php
$goipList = $client->goipList();

/** @var \PavloDotDev\GoipClient\Entities\GoipItem $item */
foreach( $goipList as $item ) {
    print_r($item);
}
```

### Получение списка входящих SMS

```php
$inboxSMSList = $client->inboxSMS();

/** @var \PavloDotDev\GoipClient\Entities\InboxSMSItem $item */
foreach( $inboxSMSList as $item ) {
    print_r($item);
}
```

### Получение списка USSD-запросов

```php
$ussdList = $client->ussdList();

/** @var \PavloDotDev\GoipClient\Entities\USSDItem $item */
foreach( $ussdList as $item ) {
    print_r($item);
}
```

### Отправка USSD запроса и получение ответа

```php
$command = '*100#';
$goipList = $client->goipList();

/** @var \PavloDotDev\GoipClient\Entities\GoipItem $item */
foreach( $goipList as $item ) {
    $answer = $client->ussd($item->termId, $command, true);
    echo "GoIP Terminal {$item->termId}: $answer\n";
}
```
