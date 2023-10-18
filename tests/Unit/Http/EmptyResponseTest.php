<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Http\EmptyResponse;

class EmptyResponseTest extends TestCase
{
    public function testEmptyResponse(): void
    {
        $response = new EmptyResponse();

        self::assertEquals('', $response->getBody()->__toString());
    }

    public function testEmptyResponseReturnsDefaultStatusCode(): void
    {
        self::assertEquals(204, (new EmptyResponse())->getStatusCode());
    }

    public function testEmptyResponseWithCustomStatusCode(): void
    {
        self::assertEquals(304, (new EmptyResponse(304))->getStatusCode());
    }

    public function testEmptyResponseWithHeaders(): void
    {
        $response = new EmptyResponse(headers: ['x-custom' => ['foo-bar']]);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
    }
}
