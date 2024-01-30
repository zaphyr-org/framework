<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Testing\Traits;

use Psr\Http\Message\ResponseInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait ResponseTrait
{
    /**
     * @param string            $name
     * @param string|null       $value
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertHeader(string $name, string|null $value, ResponseInterface $response): void
    {
        self::assertTrue($response->hasHeader($name));

        if ($value !== null) {
            self::assertEquals($value, $response->getHeaderLine($name));
        }
    }

    /**
     * @param string            $name
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertNotHasHeader(string $name, ResponseInterface $response): void
    {
        self::assertFalse($response->hasHeader($name));
    }

    /**
     * @param string            $expected
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertBody(string $expected, ResponseInterface $response): void
    {
        self::assertEquals($expected, $response->getBody()->__toString());
    }
}
