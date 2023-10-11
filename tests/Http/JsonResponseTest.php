<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http;

use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Http\Exceptions\HttpException;
use Zaphyr\Framework\Http\JsonResponse;

class JsonResponseTest extends TestCase
{
    public function testJsonResponseWithArrayData(): void
    {
        $data = [1, 2, 3];
        $response = new JsonResponse($data);

        self::assertEquals(json_encode($data), $response->getBody()->__toString());
    }

    public function testJsonResponseWithAssocArrayData(): void
    {
        $data = ['foo' => 'bar'];
        $response = new JsonResponse($data);

        self::assertEquals(json_encode($data), $response->getBody()->__toString());
    }

    public function testJsonResponseEncodeFlags(): void
    {
        $response = new JsonResponse('<>\'&"');

        $this->assertEquals('"\u003C\u003E\u0027\u0026\u0022"', $response->getBody()->__toString());
    }

    public function testJsonResponseWithObjectToStringMethod(): void
    {
        $object = new class {
            public function __toString(): string
            {
                return 'foo';
            }
        };

        $response = new JsonResponse($object);

        self::assertEquals('"foo"', $response->getBody()->__toString());
    }

    public function testJsonResponseReturnsJsonContentTypeHeader(): void
    {
        self::assertEquals('application/json', (new JsonResponse(null))->getHeaderLine('content-type'));
    }

    public function testJsonResponseReturnsDefaultStatusCode(): void
    {
        self::assertEquals(200, (new JsonResponse(null))->getStatusCode());
    }

    public function testJsonResponseWithCustomStatusCode(): void
    {
        self::assertEquals(404, (new JsonResponse(null, statusCode: 404))->getStatusCode());
    }

    public function testJsonResponseWithHeaders(): void
    {
        self::assertEquals(
            ['foo-bar'],
            (new JsonResponse(null, headers: ['x-custom' => ['foo-bar']]))->getHeader('x-custom')
        );
    }

    public function testJsonResponseWithCustomContentTypeHeader(): void
    {
        self::assertEquals(
            'foo/json',
            (new JsonResponse(null, headers: ['content-type' => 'foo/json']))->getHeaderLine('content-type')
        );
    }

    /**
     * @param mixed $value
     *
     * @dataProvider scalarValuesDataProvider
     */
    public function testJsonResponseScalarValues(mixed $value): void
    {
        self::assertEquals(json_encode($value), (new JsonResponse($value))->getBody()->__toString());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function scalarValuesDataProvider(): array
    {
        return [
            'null' => [null],
            'false' => [false],
            'true' => [true],
            'zero' => [0],
            'int' => [1],
            'zero-float' => [0.0],
            'float' => [1.1],
            'empty-string' => [''],
            'string' => ['string'],
        ];
    }

    public function testJsonResponseRewindsBodyStream(): void
    {
        $json = ['test' => 'data'];
        $response = new JsonResponse($json);
        $actual = json_decode($response->getBody()->getContents(), true);

        self::assertEquals($json, $actual);
    }

    public function testJsonResponseThrowsExceptionOnInvalidResources(): void
    {
        $this->expectException(HttpException::class);

        new JsonResponse(fopen('php://memory', 'r'));
    }

    public function testJsonResponseThrowsExceptionOnBadEmbeddedData(): void
    {
        $this->expectException(HttpException::class);

        new JsonResponse([
            'stream' => fopen('php://memory', 'r'),
        ]);
    }

    public function testJsonResponseWithObjectWithoutToStringMethodThrowsException(): void
    {
        $this->expectException(HttpException::class);

        new JsonResponse(new class {});
    }

    public function testJsonResponseThrowsExceptionOnInvalidJson(): void
    {
        $this->expectException(HttpException::class);

        new JsonResponse("\xB1\x31");
    }
}
