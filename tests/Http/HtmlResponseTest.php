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
        $body = '<html>Foo</html>';
        $response = new HtmlResponse($body);

        self::assertEquals($body, $response->getBody()->__toString());
    }

    public function testHtmlResponseReturnsHtmlContentTypeHeader(): void
    {
        self::assertEquals('text/html; charset=utf-8', (new HtmlResponse(''))->getHeaderLine('Content-Type'));
    }

    public function testHtmlResponseWithCustomContentTypeHeader(): void
    {
        self::assertEquals(
            'foo/html',
            (new HtmlResponse('', headers: ['content-type' => 'foo/html']))->getHeaderLine('content-type')
        );
    }

    public function testHtmlResponseReturnsDefaultStatusCode(): void
    {
        self::assertEquals(200, (new HtmlResponse(''))->getStatusCode());
    }

    public function testHtmlResponseWithCustomStatusCode(): void
    {
        self::assertEquals(404, (new HtmlResponse('', 404))->getStatusCode());
    }

    public function testHtmlResponseWithHeaders(): void
    {
        $response = new HtmlResponse('', headers: ['x-custom' => ['foo-bar']]);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
    }

    public function testHtmlResponseWithStreamBody(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $response = new HtmlResponse($streamMock);

        self::assertEquals($streamMock, $response->getBody());
    }

    public function testHtmlResponseRewindsBodyStream(): void
    {
        $body = '<html>Foo</html>';
        $response = new HtmlResponse($body);

        self::assertEquals($body, $response->getBody()->getContents());
    }
}
