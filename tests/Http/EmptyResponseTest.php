<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http;

use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Http\EmptyResponse;

class EmptyResponseTest extends TestCase
{
    public function testEmptyResponse(): void
    {
        $response = new EmptyResponse();

        self::assertTrue($response->isEmpty());
        self::assertEquals(204, $response->getStatusCode());
        self::assertEquals('', $response->getBody()->__toString());
    }

    public function testEmptyResponseWithCustomStatusCode(): void
    {
        $response = new EmptyResponse(304);

        self::assertTrue($response->isEmpty());
        self::assertEquals(304, $response->getStatusCode());
        self::assertEquals('', $response->getBody()->__toString());
    }
}
