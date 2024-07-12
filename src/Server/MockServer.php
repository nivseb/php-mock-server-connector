<?php

namespace Nivseb\PhpMockServerConnector\Server;

use Nivseb\PhpMockServerConnector\Exception\FailCreateExpectationException;
use Nivseb\PhpMockServerConnector\Exception\FailResetMockServerException;
use Nivseb\PhpMockServerConnector\Exception\MissingServerInitExceptionAbstract;
use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;
use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;
use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;

class MockServer
{
    protected static string $mockServerUrl = '';

    /** @var array<string, MockServerEndpoint> */
    protected static array $endpoints = [];

    protected static ?Connector $connector = null;

    /**
     * @throws FailResetMockServerException
     */
    public static function init(string $mockServerUrl): void
    {
        static::$mockServerUrl = $mockServerUrl;
        static::$endpoints     = [];
        static::reset();
    }

    /**
     * @throws FailResetMockServerException
     */
    public static function reset(): void
    {
        foreach (static::$endpoints as $endpoint) {
            $endpoint->resetExpectations();
        }

        static::$connector?->reset();
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
     * @throws FailCreateExpectationException
     * @throws MissingServerInitExceptionAbstract
     */
    public static function applyExpectation(MockServerExpectation $expectation): RemoteExpectation
    {
        return static::getConnector()->applyExpectation($expectation);
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
     * @throws VerificationFailException
     */
    public static function verify(): void
    {
        if (!static::$connector) {
            return;
        }

        foreach (static::$endpoints as $endpoint) {
            $endpoint->verify(static::$connector);
        }
    }

    /**
     * @throws UnsuccessfulVerificationException
     * @throws FailResetMockServerException
     * @throws VerificationFailException
     */
    public static function close(): void
    {
        static::verify();
        static::reset();
    }
}
