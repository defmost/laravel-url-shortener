<?php

namespace LaraCrafts\UrlShortener\Http;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\UriInterface;
//use function GuzzleHttp\json_decode;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;

class PolrShortener extends RemoteShortener
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var array
     */
    protected array $defaults;

    /**
     * Create a new Polr shortener.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string $token
     * @param string $domain
     * @return void
     */
    public function __construct(ClientInterface $client, string $token, string $domain)
    {
        $this->client = $client;
        $this->defaults = [
            'allow_redirects' => false,
            'base_uri' => $domain,
            'headers' => [
                'Accept' => 'application/json',
            ],
            'query' => [
                'key' => $token,
                'response_type' => 'json',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function shortenAsync(UriInterface|string $url, array $options = []): \GuzzleHttp\Promise\PromiseInterface
    {
        $options = array_merge_recursive(Arr::add($this->defaults, 'query.url', $url), $options);
        $request = new Request('GET', '/api/v2/action/shorten');

        return $this->client->sendAsync($request, $options)->then(function (ResponseInterface $response) {
            return json_decode($response->getBody()->getContents())->result;
        });
    }
}
