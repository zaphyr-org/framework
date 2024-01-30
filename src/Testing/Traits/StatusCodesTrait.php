<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Testing\Traits;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\Framework\Http\Utils\StatusCode;

trait StatusCodesTrait
{
    /**
     * @param int               $expected
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertStatus(int $expected, ResponseInterface $response): void
    {
        self::assertEquals($expected, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertSuccessful(ResponseInterface $response): void
    {
        self::assertTrue(
            $response->getStatusCode() >= StatusCode::OK
            && $response->getStatusCode() < StatusCode::MULTIPLE_CHOICES
        );
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertRedirect(ResponseInterface $response): void
    {
        self::assertTrue(
            $response->getStatusCode() >= StatusCode::MULTIPLE_CHOICES
            && $response->getStatusCode() < StatusCode::BAD_REQUEST
        );
    }

    /**
     * @param string            $uri
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertRedirectTo(string $uri, ResponseInterface $response): void
    {
        self::assertRedirect($response);
        self::assertHeader('Location', null, $response);
        self::assertEquals($uri, $response->getHeader('Location')[0]);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertServerError(ResponseInterface $response): void
    {
        self::assertTrue(
            $response->getStatusCode() >= StatusCode::INTERNAL_SERVER_ERROR
            && $response->getStatusCode() <= 599
        );
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertOk(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::OK, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertCreated(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::CREATED, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertAccepted(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::ACCEPTED, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertNoContent(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::NO_CONTENT, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertMovedPermanently(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::MOVED_PERMANENTLY, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertFound(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::FOUND, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertSeeOther(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::SEE_OTHER, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertNotModified(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::NOT_MODIFIED, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertTemporaryRedirect(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::TEMPORARY_REDIRECT, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertPermanentRedirect(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::PERMANENT_REDIRECT, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertBadRequest(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertUnauthorized(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertPaymentRequired(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::PAYMENT_REQUIRED, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertForbidden(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertNotFound(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertMethodNotAllowed(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertNotAcceptable(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::NOT_ACCEPTABLE, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertRequestTimeout(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::REQUEST_TIMEOUT, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertConflict(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::CONFLICT, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertGone(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::GONE, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertUnsupportedMediaType(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertUnprocessableEntity(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertTooManyRequests(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertInternalServerError(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public static function assertServiceUnavailable(ResponseInterface $response): void
    {
        self::assertEquals(StatusCode::SERVICE_UNAVAILABLE, $response->getStatusCode());
    }
}
