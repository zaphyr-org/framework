<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http\Exceptions;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Contracts\Http\Exceptions\HttpExceptionInterface;
use Zaphyr\Framework\Http\Exceptions\HttpException;

class HttpExceptionTest extends TestCase
{
    /* -------------------------------------------------
     * CONSTRUCTOR AND GETTERS
     * -------------------------------------------------
     */

    public function testConstructorAndGetters(): void
    {
        $statusCode = 404;
        $message = 'Not Found';
        $headers = ['foo' => 'bar'];
        $previous = new Exception('Previous exception');
        $code = 0;

        $httpException = new HttpException($statusCode, $message, $headers, $previous, $code);

        self::assertEquals($statusCode, $httpException->getStatusCode());
        self::assertEquals($message, $httpException->getMessage());
        self::assertEquals($headers, $httpException->getHeaders());
        self::assertSame($previous, $httpException->getPrevious());
        self::assertEquals($code, $httpException->getCode());
    }

    /* -------------------------------------------------
     * GET MESSAGE
     * -------------------------------------------------
     */

    public function testGetMessageReturnsDefaultStatusCodeMessage(): void
    {
        $httpException = new HttpException(404);

        self::assertEquals('Not Found', $httpException->getMessage());
    }

    public function testGetMessageReturnsUnknownMessageOnUnknownStatusCode(): void
    {
        $httpException = new HttpException(999);

        self::assertEquals('Unknown error', $httpException->getMessage());
    }

    /* -------------------------------------------------
     * BUILD JSON RESPONSE
     * -------------------------------------------------
     */

    public function testBuildJsonResponse(): void
    {
        $errorJson = json_encode([
            'error' => [
                'status' => $statusCode = 404,
                'message' => $message = 'Not Found',
            ]
        ]);

        $streamMock = $this->createMock(StreamInterface::class);

        $streamMock->expects(self::once())
            ->method('isWritable')
            ->willReturn(true);

        $streamMock->expects(self::once())
            ->method('write')
            ->with($errorJson);

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();

        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $responseMock->expects(self::once())
            ->method('withStatus')
            ->with($statusCode, $message)
            ->willReturnSelf();

        try {
            throw new HttpException($statusCode);
        } catch (HttpExceptionInterface $exception) {
            $exception->buildJsonResponse($responseMock);

            self::assertEquals($statusCode, $exception->getStatusCode());
            self::assertEquals($message, $exception->getMessage());
            self::assertEquals('application/json', $exception->getHeaders()['Content-Type']);
        }
    }

    public function testBuildJsonResponseWithNonWritableBody(): void
    {
        $statusCode = 404;
        $message = 'Not Found';

        $streamMock = $this->createMock(StreamInterface::class);

        $streamMock->expects(self::once())
            ->method('isWritable')
            ->willReturn(false);

        $streamMock->expects(self::never())
            ->method('write');

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();

        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $responseMock->expects(self::once())
            ->method('withStatus')
            ->with($statusCode, $message)
            ->willReturnSelf();

        try {
            throw new HttpException($statusCode);
        } catch (HttpExceptionInterface $exception) {
            $exception->buildJsonResponse($responseMock);

            self::assertEquals($statusCode, $exception->getStatusCode());
            self::assertEquals($message, $exception->getMessage());
            self::assertEquals('application/json', $exception->getHeaders()['Content-Type']);
        }
    }

    public function testBuildJsonResponseKeepsJsonContentTypeHeader(): void
    {
        $errorJson = json_encode([
            'error' => [
                'status' => $statusCode = 404,
                'message' => $message = 'Not Found',
            ]
        ]);

        $streamMock = $this->createMock(StreamInterface::class);

        $streamMock->expects(self::once())
            ->method('isWritable')
            ->willReturn(true);

        $streamMock->expects(self::once())
            ->method('write')
            ->with($errorJson);

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();

        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $responseMock->expects(self::once())
            ->method('withStatus')
            ->with($statusCode, $message)
            ->willReturnSelf();

        try {
            throw new HttpException($statusCode, headers: ['Content-Type' => 'text/html']);
        } catch (HttpExceptionInterface $exception) {
            $exception->buildJsonResponse($responseMock);

            self::assertEquals($statusCode, $exception->getStatusCode());
            self::assertEquals($message, $exception->getMessage());
            self::assertEquals('application/json', $exception->getHeaders()['Content-Type']);
        }
    }
}
