<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http;

use Zaphyr\Framework\Http\RedirectResponse;
use PHPUnit\Framework\TestCase;
use Zaphyr\HttpMessage\Uri;

class RedirectResponseTest extends TestCase
{
    public function testRedirectResponse(): void
    {
        $uri = 'https://zaphyr.org';
        $response = new RedirectResponse($uri);

        self::assertEquals($uri, $response->getHeaderLine('location'));
    }

    public function testRedirectResponseReturnsDefaultStatusCode(): void
    {
        self::assertEquals(302, (new RedirectResponse(''))->getStatusCode());
    }

    public function testRedirectResponseWithCustomStatusCode(): void
    {
        self::assertEquals(301, (new RedirectResponse('', 301))->getStatusCode());

    }

    public function testRedirectResponseUriWillOverrideHeadersLocation(): void
    {
        $response = new RedirectResponse('https://zaphyr.org', 301, [
            'location' => 'www.example.com'
        ]);

        self::assertEquals('https://zaphyr.org', $response->getHeaderLine('location'));
    }

    public function testRedirectResponseWithHeaders(): void
    {
        $response = new RedirectResponse('https://zaphyr.org', headers: ['x-custom' => ['foo-bar']]);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
    }
}
