<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http;

use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Http\TextResponse;
use PHPUnit\Framework\TestCase;

class TextResponseTest extends TestCase
{
    public function testTextResponse(): void
    {
        $response = new TextResponse($body = 'Foo');

        self::assertEquals($body, (string)$response->getBody());
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testTextResponseWithCustomStatusCode(): void
    {
        $response = new TextResponse('Foo', 404);

        self::assertEquals(404, $response->getStatusCode());
    }

    public function testTextResponseWithHeaders(): void
    {
        $response = new TextResponse('Foo', headers: ['x-custom' => ['foo-bar']]);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        self::assertEquals('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
    }

    public function testTextResponseWithStreamBody(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $response = new TextResponse($streamMock);

        self::assertSame($streamMock, $response->getBody());
    }

    public function testTextResponseRewindsBodyStream(): void
    {
        $body = 'Foo';
        $response = new TextResponse($body);

        self::assertEquals($body, $response->getBody()->getContents());
    }
}
