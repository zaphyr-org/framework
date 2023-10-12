<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Http\XmlResponse;

class XmlResponseTest extends TestCase
{
    public function testXmlResponse(): void
    {
        $body = '<?xml version="1.0"?>' . PHP_EOL . '<something>Foo</something>';
        $response = new XmlResponse($body);

        self::assertEquals($body, $response->getBody()->__toString());
    }

    public function testXmlResponseReturnsXmlContentTypeHeader(): void
    {
        self::assertEquals('application/xml; charset=utf-8', (new XmlResponse(''))->getHeaderLine('Content-Type'));
    }

    public function testXmlResponseWithCustomContentTypeHeader(): void
    {
        self::assertEquals(
            'foo/xml',
            (new XmlResponse('', headers: ['content-type' => 'foo/xml']))->getHeaderLine('content-type')
        );
    }

    public function testXmlResponseReturnsDefaultStatusCode(): void
    {
        self::assertEquals(200, (new XmlResponse(''))->getStatusCode());
    }

    public function testXmlResponseWithCustomStatusCode(): void
    {
        self::assertEquals(404, (new XmlResponse('', 404))->getStatusCode());
    }

    public function testXmlResponseWithHeaders(): void
    {
        $response = new XmlResponse('', headers: ['x-custom' => ['foo-bar']]);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
    }

    public function testXmlResponseWithStreamBody(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $response = new XmlResponse($streamMock);

        self::assertSame($streamMock, $response->getBody());
    }

    public function testXmlResponseRewindsBodyStream(): void
    {
        $body = '<?xml version="1.0"?>' . PHP_EOL . '<something>Foo</something>';
        $response = new XmlResponse($body);

        self::assertEquals($body, $response->getBody()->getContents());
    }
}
