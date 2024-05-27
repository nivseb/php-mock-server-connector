<?php

namespace Tests\Unit\Server;

use Nivseb\PhpMockServerConnector\Server\MockServer;
use Nivseb\PhpMockServerConnector\Server\MockServerEndpoint;
use function Pest\Faker\fake;

it(
    'auto register endpoint to the mock server container',
    function (): void {
        expect(MockServer::getEndpoints())->toBeArray()->toBeEmpty();
        $endpoint = new MockServerEndpoint('/my/fake/path');
        expect(MockServer::getEndpoints())->toBe(['/my/fake/path' => $endpoint]);
    }
);

it(
    'can access endpoint from server with path',
    function (): void {
        $endpoint = new MockServerEndpoint('/my/fake/path');
        expect(MockServer::getEndpoint('/my/fake/path'))->toBe($endpoint);
    }
);

it(
    'the default server url should be an empty string',
    function (): void {
        expect(MockServer::getMockServerUrl())->toBe('');
    }
);

it(
    'server url is given url for init',
    function (): void {
        $expectedUrl = fake()->url();
        MockServer::init($expectedUrl);
        expect(MockServer::getMockServerUrl())->toBe($expectedUrl);
    }
);

it(
    'second init overwrite existing url',
    function (): void {
        $expectedUrl = fake()->url();
        MockServer::init('https://notMy.url');
        MockServer::init($expectedUrl);
        expect(MockServer::getMockServerUrl())->toBe($expectedUrl);
    }
);
