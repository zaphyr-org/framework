<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Events\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Framework\Events\Http\RequestFinishedEvent;

class RequestFinishedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $requestFinishedEvent = new RequestFinishedEvent($serverRequestMock, $responseMock);

        self::assertSame($serverRequestMock, $requestFinishedEvent->getRequest());
        self::assertSame($responseMock, $requestFinishedEvent->getResponse());
    }
}
