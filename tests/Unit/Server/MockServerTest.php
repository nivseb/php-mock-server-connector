<?php

namespace Tests\Unit\Server;

use Nivseb\PhpMockServerConnector\Exception\FailResetAbstractMockServerException;
use Nivseb\PhpMockServerConnector\Exception\MissingServerInitExceptionAbstract;
use Nivseb\PhpMockServerConnector\Server\MockServer;
use Nivseb\PhpMockServerConnector\Server\MockServerEndpoint;
use function Pest\Faker\fake;

test(
    'auto register endpoint to the mock server container',
    function () {
        expect(MockServer::getEndpoints())->toBeArray()->toBeEmpty();
        $endpoint = new MockServerEndpoint('/my/fake/path');
        expect(MockServer::getEndpoints())->toBe(['/my/fake/path' => $endpoint]);
    }
);

test(
    'can access endpoint from server with path',
    function () {
        $endpoint = new MockServerEndpoint('/my/fake/path');
        expect(MockServer::getEndpoint('/my/fake/path'))->toBe( $endpoint);
    }
);

test(
    'the default server url should be an empty string',
    function() {
        expect(MockServer::getMockServerUrl())->toBe('');
    }
);

test(
'server url is given url for init',
    function() {
        $expectedUrl = fake()->url();
        MockServer::init($expectedUrl);
        expect(MockServer::getMockServerUrl())->toBe($expectedUrl);
    }
);

test(
    'second init overwrite existing url',
    function() {
        $expectedUrl = fake()->url();
        MockServer::init('https://notMy.url');
        MockServer::init($expectedUrl);
        expect(MockServer::getMockServerUrl())->toBe($expectedUrl);
    }
);
