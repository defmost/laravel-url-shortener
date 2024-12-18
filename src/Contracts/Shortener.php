<?php

namespace LaraCrafts\UrlShortener\Contracts;

interface Shortener
{
    /**
     * Shorten the given URL.
     *
     * @param \Psr\Http\Message\UriInterface|string $url
     * @param array $options
     * @return string
     */
    public function shorten(\Psr\Http\Message\UriInterface|string $url, array $options = []): string;
}
