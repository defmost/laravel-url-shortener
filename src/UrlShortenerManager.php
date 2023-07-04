<?php

namespace LaraCrafts\UrlShortener;

use Closure;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaraCrafts\UrlShortener\Contracts\Factory as FactoryContract;
use LaraCrafts\UrlShortener\Http\BitLyShortener;
use LaraCrafts\UrlShortener\Http\FirebaseShortener;
use LaraCrafts\UrlShortener\Http\IsGdShortener;
use LaraCrafts\UrlShortener\Http\OuoIoShortener;
use LaraCrafts\UrlShortener\Http\PolrShortener;
use LaraCrafts\UrlShortener\Http\ShorteStShortener;
use LaraCrafts\UrlShortener\Http\TinyUrlShortener;

/**
 * @method string shorten(\Psr\Http\Message\UriInterface|string $url, array $options = [])
 */
class UrlShortenerManager implements FactoryContract
{
    protected Application $app;
    protected array $customCreators;
    protected array $shorteners;

    /**
     * Create a new URL shortener manager instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->customCreators = [];
        $this->shorteners = [];
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }

    /**
     * Call a custom driver creator.
     *
     * @param array $config
     * @return mixed
     */
    protected function callCustomCreator(array $config): mixed
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Create an instance of the Bit.ly driver.
     *
     * @param array $config
     * @return \LaraCrafts\UrlShortener\Http\BitLyShortener
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createBitLyDriver(array $config): BitLyShortener
    {
        return new BitLyShortener(
            $this->app->make(ClientInterface::class),
            Arr::get($config, 'token'),
            Arr::get($config, 'domain', 'bit.ly')
        );
    }

    /**
     * Create an instance of the Firebase driver.
     *
     * @param array $config
     * @return \LaraCrafts\UrlShortener\Http\FirebaseShortener
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createFirebaseDriver(array $config): FirebaseShortener
    {
        return new FirebaseShortener(
            $this->app->make(ClientInterface::class),
            Arr::get($config, 'token'),
            Arr::get($config, 'prefix'),
            Arr::get($config, 'suffix')
        );
    }

    /**
     * Create an instance of the Is.gd driver.
     *
     * @param array $config
     * @return \LaraCrafts\UrlShortener\Http\IsGdShortener
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createIsGdDriver(array $config): IsGdShortener
    {
        return new IsGdShortener(
            $this->app->make(ClientInterface::class),
            Arr::get($config, 'base_uri'),
            Arr::get($config, 'statistics')
        );
    }

    /**
     * Create an instance of the Ouo.io driver.
     *
     * @param array $config
     * @return \LaraCrafts\UrlShortener\Http\OuoIoShortener
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createOuoIoDriver(array $config): OuoIoShortener
    {
        return new OuoIoShortener(
            $this->app->make(ClientInterface::class),
            Arr::get($config, 'token')
        );
    }

    /**
     * Create an instance of the Polr driver.
     *
     * @param array $config
     * @return \LaraCrafts\UrlShortener\Http\PolrShortener
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createPolrDriver(array $config): PolrShortener
    {
        return new PolrShortener(
            $this->app->make(ClientInterface::class),
            Arr::get($config, 'token'),
            Arr::get($config, 'prefix')
        );
    }

    /**
     * Create an instance of the Shorte.st driver.
     *
     * @param array $config
     * @return \LaraCrafts\UrlShortener\Http\ShorteStShortener
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createShorteStDriver(array $config): ShorteStShortener
    {
        return new ShorteStShortener(
            $this->app->make(ClientInterface::class),
            Arr::get($config, 'token')
        );
    }

    /**
     * Create an instance of the TinyURL driver.
     *
     * @return \LaraCrafts\UrlShortener\Http\TinyUrlShortener
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createTinyUrlDriver(): TinyUrlShortener
    {
        return new TinyUrlShortener($this->app->make(ClientInterface::class));
    }

    /**
     * Get a URL shortener driver instance.
     *
     * @param string|null $name
     * @return \LaraCrafts\UrlShortener\Contracts\Shortener
     */
    public function driver(string $name = null): Contracts\Shortener
    {
        return $this->shortener($name);
    }

    /**
     * Register a custom driver creator closure.
     *
     * @param string $name
     * @param \Closure $callback
     * @return $this
     */
    public function extend(string $name, Closure $callback): static
    {
        $this->customCreators[$name] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Get the default URL shortener driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->app['config']['url-shortener.default'];
    }

    /**
     * Get the URL shortener configuration.
     *
     * @param string $name
     * @return array|null
     */
    protected function getShortenerConfig(string $name): ?array
    {
        return $this->app['config']["url-shortener.shorteners.$name"];
    }

    /**
     * Resolve the given URL shortener.
     *
     * @param string $name
     * @return \LaraCrafts\UrlShortener\Contracts\Shortener
     */
    protected function resolve(string $name): Contracts\Shortener
    {
        $config = $this->getShortenerConfig($name);

        if (is_null($config) || !array_key_exists('driver', $config)) {
            throw new InvalidArgumentException("URL shortener [{$name}] is not defined");
        }

        if (array_key_exists($config['driver'], $this->customCreators)) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create' . Str::studly($config['driver']) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->$driverMethod($config);
        }
        throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported");
    }

    /**
     * Set the default URL shortener driver name.
     *
     * @param string $name
     * @return $this
     */
    public function setDefaultDriver(string $name): static
    {
        $this->app['config']['url-shortener.default'] = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function shortener(string $name = null): Contracts\Shortener
    {
        $name = $name ?: $this->getDefaultDriver();

        if (array_key_exists($name, $this->shorteners)) {
            return $this->shorteners[$name];
        }

        return $this->shorteners[$name] = $this->resolve($name);
    }
}
