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
        $response = new XmlResponse($body = '<?xml version="1.0"?>' . PHP_EOL . '<something>Foo</something>');

        self::assertSame($body, (string)$response->getBody());
        self::assertSame('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame(200, $response->getStatusCode());
    }

    public function testXmlResponseWithCustomStatusCode(): void
    {
        $response = new XmlResponse(
            $body = '<?xml version="1.0"?>' . PHP_EOL . '<something>Foo</something>',
            404
        );

        self::assertSame($body, (string)$response->getBody());
        self::assertSame('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame(404, $response->getStatusCode());
    }

    public function testXmlResponseWithHeaders(): void
    {
        $response = new XmlResponse(
            '<?xml version="1.0"?>' . PHP_EOL . '<something>Foo</something>',
            headers: ['x-custom' => ['foo-bar']]
        );

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
