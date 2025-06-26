<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Http;

use JsonSerializable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Http\Exceptions\ResponseException;
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
        $response = new JsonResponse(['encoded' => '<>\'&"']);

        $this->assertEquals('{"encoded":"\u003C\u003E\u0027\u0026\u0022"}', $response->getBody()->__toString());
    }

    public function testJsonResponseWithJsonSerializableData(): void
    {
        $data = new class implements JsonSerializable {
            public function jsonSerialize(): array
            {
                return ['test' => 'data'];
            }
        };

        $response = new JsonResponse($data);

        self::assertEquals(json_encode(['test' => 'data']), $response->getBody()->__toString());
    }

    public function testJsonResponseReturnsJsonContentTypeHeader(): void
    {
        self::assertEquals(
            'application/json; charset=utf-8',
            (new JsonResponse(['test' => 'data']))->getHeaderLine('content-type')
        );
    }

    public function testJsonResponseWithCustomContentTypeHeader(): void
    {
        self::assertEquals(
            'foo/json',
            (new JsonResponse(['test' => 'data'], headers: ['content-type' => 'foo/json']))
                ->getHeaderLine('content-type')
        );
    }

    public function testJsonResponseReturnsDefaultStatusCode(): void
    {
        self::assertEquals(200, (new JsonResponse(['test' => 'data']))->getStatusCode());
    }

    public function testJsonResponseWithCustomStatusCode(): void
    {
        self::assertEquals(404, (new JsonResponse(['test' => 'data'], statusCode: 404))->getStatusCode());
    }

    public function testJsonResponseWithHeaders(): void
    {
        self::assertEquals(
            ['foo-bar'],
            (new JsonResponse(['test' => 'data'], headers: ['x-custom' => ['foo-bar']]))->getHeader('x-custom')
        );
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('scalarValuesDataProvider')]
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
            'null' => [['null' => null]],
            'false' => [['false' => false]],
            'true' => [['true' => true]],
            'zero' => [['zero' => 0]],
            'int' => [['int' => 1]],
            'zero-float' => [['zero-float' => 0.0]],
            'float' => [['float' => 1.1]],
            'empty-string' => [['empty-string' => '']],
            'string' => [['string' => 'string']],
        ];
    }

    public function testJsonResponseRewindsBodyStream(): void
    {
        $json = ['test' => 'data'];
        $response = new JsonResponse($json);
        $actual = json_decode($response->getBody()->getContents(), true);

        self::assertEquals($json, $actual);
    }

    public function testJsonResponseThrowsExceptionOnBadEmbeddedData(): void
    {
        $this->expectException(ResponseException::class);

        new JsonResponse([
            'stream' => fopen('php://memory', 'r'),
        ]);
    }
}
