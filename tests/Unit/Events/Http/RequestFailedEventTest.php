<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Events\Http;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Framework\Events\Http\RequestFailedEvent;

class RequestFailedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $exception = new Exception('Whoops');

        $requestFailedEvent = new RequestFailedEvent($serverRequestMock, $exception);

        self::assertSame($serverRequestMock, $requestFailedEvent->getRequest());
        self::assertSame($exception, $requestFailedEvent->getError());
    }
}
