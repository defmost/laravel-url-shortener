<?php

namespace LaraCrafts\UrlShortener\Http;

use LaraCrafts\UrlShortener\Contracts\AsyncShortener;
use Psr\Http\Message\UriInterface;

abstract class RemoteShortener implements AsyncShortener
{
    /**
     * {@inheritDoc}
     */
    public function shorten(UriInterface|string $url, array $options = []): string
    {
        return $this->shortenAsync($url, $options)->wait();
    }
}
