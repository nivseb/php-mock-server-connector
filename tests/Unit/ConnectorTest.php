<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Nivseb\PhpMockServerConnector\Exception\FailResetMockServerException;
use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;
use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;
use Nivseb\PhpMockServerConnector\Server\Connector;
use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;
use function Pest\Faker\fake;

it(
    'rest mock server correctly',
    function (): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(['/mockserver/reset'])
            ->andReturn(new Response());

        $testConnector->reset();
    }
);

it(
    'reset throw exception for non 200 response status code',
    function (int $statusCode): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $response              = new Response($statusCode);
        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(['/mockserver/reset'])
            ->andReturn($response);

        expect(fn () => $testConnector->reset())
            ->toThrow(
                function (FailResetMockServerException $exception) use ($response): void {
                    expect($exception->getMessage())->toBe('Failing to reset mock server expectations!');
                    expect($exception->response)->toBe($response);
                    expect($exception->getPrevious())->toBeNull();
                }
            );
    }
)->with([
    'created'      => [201],
    'not modified' => [304],
    'not found'    => [404],
    'server fail'  => [500],
]);

it(
    'reset fail with guzzle client exception',
    function (): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $guzzleException       = new TransferException();
        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(['/mockserver/reset'])
            ->andThrow($guzzleException);

        expect(fn () => $testConnector->reset())
            ->toThrow(
                function (FailResetMockServerException $exception) use ($guzzleException): void {
                    expect($exception->getMessage())->toBe('Failing to reset mock server expectations!');
                    expect($exception->response)->toBeNull();
                    expect($exception->getPrevious())->toBe($guzzleException);
                }
            );
    }
);

it(
    'verify remote expectation correctly',
    function (): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $remoteExpectation = new RemoteExpectation(
            fake()->uuid,
            new MockServerExpectation('METHOD', '/path')
        );

        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(
                [
                    '/mockserver/verify',
                    [
                        'json' => [
                            'expectationId' => [
                                'id' => $remoteExpectation->uuid,
                            ],
                            'times' => [
                                'atLeast' => 1,
                                'atMost'  => 1,
                            ],
                        ],
                    ],
                ]
            )
            ->andReturn(new Response(202));

        $testConnector->verify($remoteExpectation);
    }
);

it(
    'verify remote expectation verify multiple times expectation correct',
    function (): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $times             = fake()->numberBetween(1, 100);
        $remoteExpectation = new RemoteExpectation(
            fake()->uuid,
            new MockServerExpectation('METHOD', '/path', times: $times)
        );

        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(
                [
                    '/mockserver/verify',
                    [
                        'json' => [
                            'expectationId' => [
                                'id' => $remoteExpectation->uuid,
                            ],
                            'times' => [
                                'atLeast' => $times,
                                'atMost'  => $times,
                            ],
                        ],
                    ],
                ]
            )
            ->andReturn(new Response(202));

        $testConnector->verify($remoteExpectation);
    }
);

it(
    'verify remote expectation correctly but receive bad result',
    function (): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $remoteExpectation = new RemoteExpectation(
            fake()->uuid,
            new MockServerExpectation('METHOD', '/path')
        );

        $body     = 'Request not found exactly 1 times, expected:<{';
        $response = new Response(
            406,
            headers: ['Content-Length' => strlen($body)],
            body: $body
        );
        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(
                [
                    '/mockserver/verify',
                    [
                        'json' => [
                            'expectationId' => [
                                'id' => $remoteExpectation->uuid,
                            ],
                            'times' => [
                                'atLeast' => 1,
                                'atMost'  => 1,
                            ],
                        ],
                    ],
                ]
            )
            ->andReturn($response);

        expect(fn () => $testConnector->verify($remoteExpectation))
            ->toThrow(
                function (UnsuccessfulVerificationException $exception) use ($remoteExpectation, $response): void {
                    expect($exception->getMessage())->toBe('Request not found exactly 1 times');
                    expect($exception->expectation)->toBe($remoteExpectation);
                    expect($exception->response)->toBe($response);
                }
            );
    }
);

it(
    'verify remote expectation correctly but receive bad result with guzzle exception',
    function (): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $remoteExpectation = new RemoteExpectation(
            fake()->uuid,
            new MockServerExpectation('METHOD', '/path')
        );

        $body     = 'Request not found exactly 1 times, expected:<{';
        $response = new Response(
            406,
            headers: ['Content-Length' => strlen($body)],
            body: $body
        );
        $exception             = new RequestException('Exception', new Request('METHOD', '/path'), $response);
        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(
                [
                    '/mockserver/verify',
                    [
                        'json' => [
                            'expectationId' => [
                                'id' => $remoteExpectation->uuid,
                            ],
                            'times' => [
                                'atLeast' => 1,
                                'atMost'  => 1,
                            ],
                        ],
                    ],
                ]
            )
            ->andThrow($exception);

        expect(fn () => $testConnector->verify($remoteExpectation))
            ->toThrow(
                function (UnsuccessfulVerificationException $exception) use ($remoteExpectation, $response): void {
                    expect($exception->getMessage())->toBe('Request not found exactly 1 times');
                    expect($exception->expectation)->toBe($remoteExpectation);
                    expect($exception->response)->toBe($response);
                }
            );
    }
);

it(
    'verify fail guzzle exception',
    function (): void {
        $testConnector = new class() extends Connector {
            public ?Client $client;

            public function __construct() {}
        };

        $remoteExpectation = new RemoteExpectation(
            fake()->uuid,
            new MockServerExpectation('METHOD', '/path')
        );

        $expectedException     = new TransferException();
        $testConnector->client = Mockery::mock(Client::class);
        $testConnector->client
            ->allows('put')
            ->once()
            ->withArgs(
                [
                    '/mockserver/verify',
                    [
                        'json' => [
                            'expectationId' => [
                                'id' => $remoteExpectation->uuid,
                            ],
                            'times' => [
                                'atLeast' => 1,
                                'atMost'  => 1,
                            ],
                        ],
                    ],
                ]
            )
            ->andThrow($expectedException);

        expect(fn () => $testConnector->verify($remoteExpectation))
            ->toThrow(
                function (VerificationFailException $exception) use ($remoteExpectation, $expectedException): void {
                    expect($exception->getMessage())->toBe('Fail to check verification for expectation!');
                    expect($exception->expectation)->toBe($remoteExpectation);
                    expect($exception->getPrevious())->toBe($expectedException);
                }
            );
    }
);
