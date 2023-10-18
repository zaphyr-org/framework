<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Http\TextResponse;

class TextResponseTest extends TestCase
{
    public function testTextResponse(): void
    {
        $body = 'Foo';
        $response = new TextResponse($body);

        self::assertEquals($body, $response->getBody()->__toString());
    }

    public function testTextResponseReturnsTextContentTypeHeader(): void
    {
        self::assertEquals('text/plain; charset=utf-8', (new TextResponse(''))->getHeaderLine('Content-Type'));
    }

    public function testTextResponseWithCustomContentTypeHeader(): void
    {
        self::assertEquals(
            'foo/plain',
            (new TextResponse('', headers: ['content-type' => 'foo/plain']))->getHeaderLine('content-type')
        );
    }

    public function testTextResponseReturnsDefaultStatusCode(): void
    {
        self::assertEquals(200, (new TextResponse(''))->getStatusCode());
    }

    public function testTextResponseWithCustomStatusCode(): void
    {
        self::assertEquals(404, (new TextResponse('', 404))->getStatusCode());
    }

    public function testTextResponseWithHeaders(): void
    {
        $response = new TextResponse('Foo', headers: ['x-custom' => ['foo-bar']]);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
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
