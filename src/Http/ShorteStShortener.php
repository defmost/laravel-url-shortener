<?php

namespace LaraCrafts\UrlShortener\Http;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\UriInterface;
//use function GuzzleHttp\json_decode;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class ShorteStShortener extends RemoteShortener
{
    protected ClientInterface $client;
    protected array $defaults;

    /**
     * Create a new Shorte.st shortener.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string $token
     * @return void
     */
    public function __construct(ClientInterface $client, string $token)
    {
        $this->client = $client;
        $this->defaults = [
            'allow_redirects' => false,
            'base_uri' => 'https://api.shorte.st',
            'headers' => [
                'Accept' => 'application/json',
                'Public-API-Token' => $token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function shortenAsync(UriInterface|string $url, array $options = []): \GuzzleHttp\Promise\PromiseInterface
    {
        $options = array_merge_recursive($this->defaults, $options);
        $request = new Request('PUT', '/v1/data/url', [], http_build_query(['urlToShorten' => $url]));

        return $this->client->sendAsync($request, $options)->then(function (ResponseInterface $response) {
            return json_decode($response->getBody()->getContents())->shortenedUrl;
        });
    }
}
