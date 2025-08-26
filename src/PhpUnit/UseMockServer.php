<?php

namespace Nivseb\PhpMockServerConnector\PhpUnit;

use Nivseb\PhpMockServerConnector\Exception\FailResetMockServerException;
use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;
use Nivseb\PhpMockServerConnector\Server\MockServer;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait UseMockServer
{
    /**
     * @throws FailResetMockServerException
     */
    protected function initMockServer(string $mockServerUrl): void
    {
        MockServer::init($mockServerUrl);
    }

    /**
     * @throws UnsuccessfulVerificationException
     * @throws FailResetMockServerException
     * @throws VerificationFailException
     *
     * @after
     */
    #[After]
    protected function closeMockServer(): void
    {
        $this->addToAssertionCount(MockServer::getAssertionCount());

        MockServer::close();
    }
}
