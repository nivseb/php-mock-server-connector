<?php

namespace Nivseb\PhpMockServerConnector\Server;

use Nivseb\PhpMockServerConnector\Exception\FailResetMockServerException;
use Nivseb\PhpMockServerConnector\Exception\MissingServerInitExceptionAbstract;
use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;

class MockServer
{
    protected static string $mockServerUrl = '';

    /** @var array<string, MockServerEndpoint> */
    protected static array $endpoints = [];

    protected static ?Connector $connector = null;

    /**
     * @throws FailResetMockServerException
     * @throws MissingServerInitExceptionAbstract
     */
    public static function init(string $mockServerUrl): void
    {
        static::$mockServerUrl = $mockServerUrl;
        static::$endpoints     = [];
        static::reset();
    }

    /**
     * @throws FailResetMockServerException
     * @throws MissingServerInitExceptionAbstract
     */
    public static function reset(): void
    {
        foreach (static::$endpoints as $endpoint) {
            $endpoint->resetExpectations();
        }

        if (static::$connector) {
            static::getConnector()->reset();
        }
    }

    public static function getMockServerUrl(): string
    {
        return static::$mockServerUrl;
    }

    public static function registerEndpoint(MockServerEndpoint $endpoint): void
    {
        static::$endpoints[$endpoint->getBasePath()] = $endpoint;
    }

    /**
     * @return array<string, MockServerEndpoint>
     */
    public static function getEndpoints(): array
    {
        return static::$endpoints;
    }

    public static function getEndpoint(string $basePath): ?MockServerEndpoint
    {
        return static::$endpoints[$basePath] ?? null;
    }

    /**
     * @throws MissingServerInitExceptionAbstract
     */
    public static function getConnector(): Connector
    {
        if (!static::$mockServerUrl) {
            throw new MissingServerInitExceptionAbstract();
        }

        if (static::$connector) {
            return static::$connector;
        }

        static::$connector = new Connector(static::$mockServerUrl);

        return static::$connector;
    }

    /**
     * @throws UnsuccessfulVerificationException
     * @throws MissingServerInitExceptionAbstract
     * @throws VerificationFailException
     */
    public static function verify(): void
    {
        foreach (static::$endpoints as $endpoint) {
            $endpoint->verify();
        }
    }

    /**
     * @throws UnsuccessfulVerificationException
     * @throws FailResetMockServerException
     * @throws VerificationFailException
     * @throws MissingServerInitExceptionAbstract
     */
    public static function close(): void
    {
        static::verify();
        static::reset();
    }
}
