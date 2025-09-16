<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Expectation;
use Mockery\MockInterface;
use Nivseb\PhpMockServerConnector\Exception\FailResetMockServerException;
use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;
use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;
use Nivseb\PhpMockServerConnector\Server\Connector;
use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;

use function Pest\Faker\fake;

it(
    'rest mock server correctly',
    /**
     * @throws FailResetMockServerException
     */
    function (): void {
        $testConnector = new class('') extends Connector {
            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(['/mockserver/reset'])
                    ->andReturn(new Response());

                return $clientMock;
            }
        };

        $testConnector->reset();
    }
);

it(
    'reset throw exception for non 200 response status code',
    /**
     * @throws FailResetMockServerException
     */
    function (int $statusCode): void {
        $response      = new Response($statusCode);
        $testConnector = new class($response) extends Connector {
            public function __construct(
                protected Response $response
            ) {
                parent::__construct('');
            }

            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(['/mockserver/reset'])
                    ->andReturn($this->response);

                return $clientMock;
            }
        };

        expect(fn () => $testConnector->reset())
            ->toThrow(
                function (FailResetMockServerException $exception) use ($response): void {
                    expect($exception->getMessage())
                        ->toBe('Failing to reset mock server expectations!')
                        ->and($exception->response)->toBe($response)
                        ->and($exception->getPrevious())->toBeNull();
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
    /**
     * @throws FailResetMockServerException
     */
    function (): void {
        $guzzleException = new TransferException();
        $testConnector   = new class($guzzleException) extends Connector {
            public function __construct(
                protected TransferException $guzzleException
            ) {
                parent::__construct('');
            }

            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(['/mockserver/reset'])
                    ->andThrow($this->guzzleException);

                return $clientMock;
            }
        };

        expect(fn () => $testConnector->reset())
            ->toThrow(
                function (FailResetMockServerException $exception) use ($guzzleException): void {
                    expect($exception->getMessage())->toBe('Failing to reset mock server expectations!')
                        ->and($exception->response)->toBeNull()
                        ->and($exception->getPrevious())->toBe($guzzleException);
                }
            );
    }
);

it(
    'verify remote expectation correctly',
    /**
     * @throws UnsuccessfulVerificationException
     * @throws VerificationFailException
     */
    function (): void {
        $remoteExpectation = new RemoteExpectation(
            fake()->uuid(),
            new MockServerExpectation('METHOD', '/path')
        );
        $testConnector = new class($remoteExpectation->uuid) extends Connector {
            public function __construct(
                protected string $uuid
            ) {
                parent::__construct('');
            }

            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(
                        [
                            '/mockserver/verify',
                            [
                                'json' => [
                                    'expectationId' => [
                                        'id' => $this->uuid,
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

                return $clientMock;
            }
        };

        $testConnector->verify($remoteExpectation);
    }
);

it(
    'verify remote expectation verify multiple times expectation correct',
    /**
     * @throws UnsuccessfulVerificationException
     * @throws VerificationFailException
     */
    function (): void {
        $atLeast           = fake()->numberBetween(1, 50);
        $atMost            = $atLeast + fake()->numberBetween(1, 50);
        $remoteExpectation = new RemoteExpectation(
            fake()->uuid(),
            new MockServerExpectation('METHOD', '/path', atLeast: $atLeast, atMost: $atMost)
        );
        $testConnector = new class($remoteExpectation->uuid, $atLeast, $atMost) extends Connector {
            public function __construct(
                protected string $uuid,
                protected int $atLeast,
                protected int $atMost,
            ) {
                parent::__construct('');
            }

            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(
                        [
                            '/mockserver/verify',
                            [
                                'json' => [
                                    'expectationId' => [
                                        'id' => $this->uuid,
                                    ],
                                    'times' => [
                                        'atLeast' => $this->atLeast,
                                        'atMost'  => $this->atMost,
                                    ],
                                ],
                            ],
                        ]
                    )
                    ->andReturn(new Response(202));

                return $clientMock;
            }
        };

        $testConnector->verify($remoteExpectation);
    }
);

it(
    'verify remote expectation correctly but receive bad result',
    /**
     * @throws UnsuccessfulVerificationException
     * @throws VerificationFailException
     */
    function (): void {
        $remoteExpectation = new RemoteExpectation(
            fake()->uuid(),
            new MockServerExpectation('METHOD', '/path')
        );

        $body     = 'Request not found exactly 1 times, expected:<{';
        $response = new Response(
            406,
            headers: ['Content-Length' => (string) strlen($body)],
            body: $body
        );

        $testConnector = new class($remoteExpectation->uuid, $response) extends Connector {
            public function __construct(
                protected string $uuid,
                protected Response $response
            ) {
                parent::__construct('');
            }

            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(
                        [
                            '/mockserver/verify',
                            [
                                'json' => [
                                    'expectationId' => [
                                        'id' => $this->uuid,
                                    ],
                                    'times' => [
                                        'atLeast' => 1,
                                        'atMost'  => 1,
                                    ],
                                ],
                            ],
                        ]
                    )
                    ->andReturn($this->response);

                return $clientMock;
            }
        };

        expect(fn () => $testConnector->verify($remoteExpectation))
            ->toThrow(
                function (UnsuccessfulVerificationException $exception) use ($remoteExpectation, $response): void {
                    expect($exception->getMessage())
                        ->toBe('Request not found exactly 1 times')
                        ->and($exception->expectation)->toBe($remoteExpectation)
                        ->and($exception->response)->toBe($response);
                }
            );
    }
);

it(
    'verify remote expectation correctly but receive bad result with guzzle exception',
    /**
     * @throws UnsuccessfulVerificationException
     * @throws VerificationFailException
     */
    function (): void {
        $remoteExpectation = new RemoteExpectation(
            fake()->uuid(),
            new MockServerExpectation('METHOD', '/path')
        );

        $body     = 'Request not found exactly 1 times, expected:<{';
        $response = new Response(
            406,
            headers: ['Content-Length' => (string) strlen($body)],
            body: $body
        );
        $exception     = new RequestException('Exception', new Request('METHOD', '/path'), $response);
        $testConnector = new class($remoteExpectation->uuid, $exception) extends Connector {
            public function __construct(
                protected string $uuid,
                protected RequestException $exception
            ) {
                parent::__construct('');
            }

            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(
                        [
                            '/mockserver/verify',
                            [
                                'json' => [
                                    'expectationId' => [
                                        'id' => $this->uuid,
                                    ],
                                    'times' => [
                                        'atLeast' => 1,
                                        'atMost'  => 1,
                                    ],
                                ],
                            ],
                        ]
                    )
                    ->andThrow($this->exception);

                return $clientMock;
            }
        };

        expect(fn () => $testConnector->verify($remoteExpectation))
            ->toThrow(
                function (UnsuccessfulVerificationException $exception) use ($remoteExpectation, $response): void {
                    expect($exception->getMessage())
                        ->toBe('Request not found exactly 1 times')
                        ->and($exception->expectation)->toBe($remoteExpectation)
                        ->and($exception->response)->toBe($response);
                }
            );
    }
);

it(
    'verify fail guzzle exception',
    /**
     * @throws UnsuccessfulVerificationException
     * @throws VerificationFailException
     */
    function (): void {
        $remoteExpectation = new RemoteExpectation(
            fake()->uuid(),
            new MockServerExpectation('METHOD', '/path')
        );

        $expectedException = new TransferException();

        $testConnector = new class($remoteExpectation->uuid, $expectedException) extends Connector {
            public function __construct(
                protected string $uuid,
                protected TransferException $exception
            ) {
                parent::__construct('');
            }

            protected function buildClient(string $mockServerUrl): Client
            {
                /** @var Client&MockInterface $clientMock */
                $clientMock = Mockery::mock(Client::class);

                /** @var Expectation $expectation */
                $expectation = $clientMock->allows('put');
                $expectation
                    ->once()
                    ->withArgs(
                        [
                            '/mockserver/verify',
                            [
                                'json' => [
                                    'expectationId' => [
                                        'id' => $this->uuid,
                                    ],
                                    'times' => [
                                        'atLeast' => 1,
                                        'atMost'  => 1,
                                    ],
                                ],
                            ],
                        ]
                    )
                    ->andThrow($this->exception);

                return $clientMock;
            }
        };

        expect(fn () => $testConnector->verify($remoteExpectation))
            ->toThrow(
                function (VerificationFailException $exception) use ($remoteExpectation, $expectedException): void {
                    expect($exception->getMessage())
                        ->toBe('Fail to check verification for expectation!')
                        ->and($exception->expectation)->toBe($remoteExpectation)
                        ->and($exception->getPrevious())->toBe($expectedException);
                }
            );
    }
);
