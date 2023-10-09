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
        $response = new RedirectResponse('https://zaphyr.org');

        self::assertTrue($response->isRedirect());
        self::assertEquals(302, $response->getStatusCode());
        self::assertEquals('https://zaphyr.org', $response->getHeaderLine('location'));
    }

    public function testRedirectResponseWithCustomStatusCode(): void
    {
        $uri = new Uri('https://zaphyr.org');
        $response = new RedirectResponse($uri, 301);

        self::assertTrue($response->isRedirect());
        self::assertEquals(301, $response->getStatusCode());
        self::assertEquals('https://zaphyr.org', $response->getHeaderLine('location'));
    }

    public function testRedirectResponseUriWillOverrideHeadersLocation(): void
    {
        $response = new RedirectResponse('https://zaphyr.org', 301, [
            'location' => 'www.example.com'
        ]);

        self::assertEquals('https://zaphyr.org', $response->getHeaderLine('location'));
    }
}
