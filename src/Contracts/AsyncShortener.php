<?php

namespace LaraCrafts\UrlShortener\Contracts;

interface AsyncShortener extends Shortener
{
    /**
     * Shorten the given URL asynchronously.
     *
     * @param \Psr\Http\Message\UriInterface|string $url
     * @param array $options
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function shortenAsync(\Psr\Http\Message\UriInterface|string $url, array $options = []): \GuzzleHttp\Promise\PromiseInterface;
}
