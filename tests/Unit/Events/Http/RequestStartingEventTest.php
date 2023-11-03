<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Events\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Framework\Events\Http\RequestStartingEvent;

class RequestStartingEventTest extends TestCase
{
    public function testEvent(): void
    {
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);

        $requestStartingEvent = new RequestStartingEvent($serverRequestMock);

        self::assertSame($serverRequestMock, $requestStartingEvent->getRequest());
    }
}
