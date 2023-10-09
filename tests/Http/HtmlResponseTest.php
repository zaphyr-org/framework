<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http;

use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Http\HtmlResponse;
use PHPUnit\Framework\TestCase;

class HtmlResponseTest extends TestCase
{
    public function testHtmlResponse(): void
    {
        $response = new HtmlResponse($body = '<html>Foo</html>');

        self::assertSame($body, (string)$response->getBody());
        self::assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame(200, $response->getStatusCode());
    }

    public function testHtmlResponseWithCustomStatusCode(): void
    {
        $response = new HtmlResponse($body = '<html>Foo</html>', 404);

        self::assertSame($body, (string)$response->getBody());
        self::assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame(404, $response->getStatusCode());
    }

    public function testHtmlResponseWithHeaders(): void
    {
        $response = new HtmlResponse('<html>Foo</html>', headers: ['x-custom' => ['foo-bar']]);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
    }

    public function testHtmlResponseWithStreamBody(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $response = new HtmlResponse($streamMock);

        self::assertSame($streamMock, $response->getBody());
    }

    public function testHtmlResponseRewindsBodyStream(): void
    {
        $body = '<html>Foo</html>';
        $response = new HtmlResponse($body);

        self::assertEquals($body, $response->getBody()->getContents());
    }
}
