<?php

namespace PavloDotDev\GoipClient\Abstract;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class Client
{
    protected readonly string $baseUri;
    protected readonly \GuzzleHttp\Client $client;
    protected bool $authorized = false;
    protected ?string $content = null;
    protected ?ResponseInterface $response = null;

    public function __construct(string $baseUri, string $login, string $password, string $cookiePath = null)
    {
        $this->baseUri = $baseUri.(mb_substr($baseUri, 0, -1) !== '/' ? '/' : '');

        if ($cookiePath) {
            $cookieJar = new FileCookieJar($cookiePath, true);
        } else {
            $cookieJar = new CookieJar();
        }

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUri,
            'cookies' => $cookieJar,
            'timeout' => 30
        ]);

        $this->auth($login, $password);
    }

    public function auth(string $login, string $password): void
    {
        $crawler = $this->request('main.php');
        if ($crawler->filter('form[name="login"]')->count() === 0) {
            $this->authorized = true;
            return;
        }

        $form = $crawler->filter('form[name="login"]')->form();
        $formValues = $form->getValues();
        $formValues['username'] = $login;
        $formValues['password'] = $password;
        $formValues['submit'] = 'Sign in';

        $crawler = $this->request($form->getUri(), null, $formValues);

        if (mb_strpos($crawler->filter('title')->html(), 'Error') !== false) {
            $errorText = $crawler->filter('li')->text();
            throw new \Exception($errorText);
        }

        if ($crawler->filter('frameset')->count() === 0) {
            throw new \Exception('Error authorization');
        }

        $this->authorized = true;
    }

    public function request(string $path, array $get = null, array $post = null): Crawler
    {
        if ($get) {
            $path .= '?'.http_build_query($get);
        }

        $this->response = $this->client->request($post ? 'POST' : 'GET', $path, [
            'form_params' => $post
        ]);

        $this->content = $this->response->getBody()->getContents();

        $crawler = new Crawler($this->content, $this->baseUri);

        if ($this->authorized && $crawler->filter('form[name="login"]')->count() > 0) {
            throw new \Exception('You are not authorized!');
        }

        return $crawler;
    }
}
