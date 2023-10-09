<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http;

use Zaphyr\Framework\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /* -------------------------------------------------
     * TO STRING
     * -------------------------------------------------
     */

    public function testToString(): void
    {
        $output = "HTTP/1.1 200 OK\r\n" . "X-Foo: Bar\r\n\r\n" . 'foo=bar';

        $this->expectOutputString($output);

        $response = new Response(statusCode: 200, headers: ['X-Foo' => 'Bar']);
        $response->getBody()->write('foo=bar');

        echo $response;
    }

    /* -------------------------------------------------
     * IS EMPTY
     * -------------------------------------------------
     */

    /**
     * @param int $statusCode
     *
     * @dataProvider emptyStatusCodesDataProvider
     */
    public function testIsEmpty(int $statusCode): void
    {
        self::assertTrue((new Response(statusCode: $statusCode))->isEmpty());
    }

    /**
     * @return array<string, int[]>
     */
    public static function emptyStatusCodesDataProvider(): array
    {
        return [
            'no-content' => [204],
            'not-modified' => [304],
        ];
    }

    /* -------------------------------------------------
     * IS INFORMATIONAL
     * -------------------------------------------------
     */

    /**
     * @param int $statusCode
     *
     * @dataProvider informationalCodesDataProvider
     */
    public function testIsInformational(int $statusCode): void
    {
        self::assertTrue((new Response(statusCode: $statusCode))->isInformational());
    }

    /**
     * @return array<string, int[]>
     */
    public static function informationalCodesDataProvider(): array
    {
        return [
            'continue' => [100],
            'switching-protocols' => [101],
            'processing' => [102],
        ];
    }

    /* -------------------------------------------------
     * IS OK
     * -------------------------------------------------
     */

    public function testIsOk(): void
    {
        self::assertTrue((new Response())->isOk());
    }

    /* -------------------------------------------------
     * IS SUCCESSFUL
     * -------------------------------------------------
     */

    /**
     * @param int $statusCode
     *
     * @dataProvider successfulCodesDataProvider
     */
    public function testIsSuccessful(int $statusCode): void
    {
        self::assertTrue((new Response(statusCode: $statusCode))->isSuccessful());
    }

    /**
     * @return array<string, int[]>
     */
    public static function successfulCodesDataProvider(): array
    {
        return [
            'ok' => [200],
            'created' => [201],
            'accepted' => [202],
            'non-authoritative-information' => [203],
            'no-content' => [204],
            'reset-content' => [205],
            'partial-content' => [206],
            'multi-status' => [207],
            'already-reported' => [208],
            'im-used' => [226],
        ];
    }

    /* -------------------------------------------------
     * IS REDIRECT
     * -------------------------------------------------
     */

    /**
     * @param int $statusCode
     *
     * @dataProvider redirectCodesDataProvider
     */
    public function testIsRedirect(int $statusCode): void
    {
        self::assertTrue((new Response(statusCode: $statusCode))->isRedirect());
    }

    /**
     * @return array<string, int[]>
     */
    public static function redirectCodesDataProvider(): array
    {
        return [
            'created' => [201],
            'moved-permanently' => [301],
            'found' => [302],
            'see-other' => [303],
            'temporary-redirect' => [307],
            'permanent-redirect' => [308],
        ];
    }

    /* -------------------------------------------------
     * IS REDIRECTION
     * -------------------------------------------------
     */

    /**
     * @param int $statusCode
     *
     * @dataProvider redirectionCodesDataProvider
     */
    public function testIsRedirection(int $statusCode): void
    {
        self::assertTrue((new Response(statusCode: $statusCode))->isRedirection());
    }

    /**
     * @return array<string, array<int, int>>
     */
    public static function redirectionCodesDataProvider(): array
    {
        return [
            'multiple-choices' => [300],
            'moved-permanently' => [301],
            'found' => [302],
            'see-other' => [303],
            'not-modified' => [304],
            'use-proxy' => [305],
            'unused' => [306],
            'temporary-redirect' => [307],
            'permanent-redirect' => [308],
        ];
    }

    /* -------------------------------------------------
     * IS FORBIDDEN
     * -------------------------------------------------
     */

    public function testIsForbidden(): void
    {
        self::assertTrue((new Response(statusCode: 403))->isForbidden());
    }

    /* -------------------------------------------------
     * IS NOT FOUND
     * -------------------------------------------------
     */

    public function testIsNotFound(): void
    {
        self::assertTrue((new Response(statusCode: 404))->isNotFound());
    }

    /* -------------------------------------------------
     * IS CLIENT ERROR
     * -------------------------------------------------
     */

    /**
     * @param int $statusCode
     *
     * @dataProvider clientErrorCodesDataProvider
     */
    public function testIsClientError(int $statusCode): void
    {
        self::assertTrue((new Response(statusCode: $statusCode))->isClientError());
    }

    /**
     * @return array<string, int[]>
     */
    public static function clientErrorCodesDataProvider(): array
    {
        return [
            'bad-request' => [400],
            'unauthorized' => [401],
            'payment-required' => [402],
            'forbidden' => [403],
            'not-found' => [404],
            'method-not-allowed' => [405],
            'not-acceptable' => [406],
            'proxy-authentication-required' => [407],
            'request-timeout' => [408],
            'conflict' => [409],
            'gone' => [410],
            'length-required' => [411],
            'precondition-failed' => [412],
            'request-entity-too-large' => [413],
            'request-uri-too-long' => [414],
            'unsupported-media-type' => [415],
            'requested-range-not-satisfiable' => [416],
            'expectation-failed' => [417],
            'im-a-teapot' => [418],
            'misdirected-request' => [421],
            'locked' => [423],
            'failed-dependency' => [424],
            'upgrade-required' => [426],
            'sprecondition-required' => [428],
            'too-many-requests' => [429],
            'request-header-fields-too-large' => [431],
            'connection-closed-without-response' => [444],
            'unavailable-for-legal-reasons' => [451],
            'client-closed-request' => [499],
        ];
    }

    /* -------------------------------------------------
     * IS SERVER ERROR
     * -------------------------------------------------
     */

    /**
     * @param int $statusCode
     *
     * @dataProvider serverErrorCodesDataProvider
     */
    public function testIsServerError(int $statusCode): void
    {
        self::assertTrue((new Response(statusCode: $statusCode))->isServerError());
    }

    /**
     * @return array<string, array<int, int>>
     */
    public static function serverErrorCodesDataProvider(): array
    {
        return [
            'internal-server-error' => [500],
            'not-implemented' => [501],
            'bad-gateway' => [502],
            'service-unavailable' => [503],
            'gateway-timeout' => [504],
            'version-not-supported' => [505],
            'variant-also-negotiates' => [506],
            'insufficient-storage' => [507],
            'loop-detected' => [508],
            'not-extended' => [510],
            'network-authentication-required' => [511],
            'network-connection-timeout' => [599],
        ];
    }
}
