<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Http\Utils;

use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Http\Exceptions\UploadedFileException;
use Zaphyr\Framework\Http\Utils\HttpUtils;
use Zaphyr\HttpMessage\UploadedFile;
use Zaphyr\HttpMessage\Uri;

class HttpUtilsTest extends TestCase
{
    /* -------------------------------------------------
     * GET URI FROM GLOBALS
     * -------------------------------------------------
     */

    /**
     * @param string $expected
     * @param array<string, array<int, mixed>> $serverParams
     *
     * @dataProvider uriFromGlobalsDataProvider
     */
    public function testFromGlobals(string $expected, array $serverParams): void
    {
        self::assertEquals((new Uri($expected))->__toString(), HttpUtils::getUrifromGlobals($serverParams));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function uriFromGlobalsDataProvider(): array
    {
        $server = [
            'REQUEST_URI' => '/blog/article.php?id=10&user=foo',
            'SERVER_PORT' => '443',
            'SERVER_ADDR' => '217.112.82.20',
            'SERVER_NAME' => 'www.example.org',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING' => 'id=10&user=foo',
            'DOCUMENT_ROOT' => '/path/to/your/server/root/',
            'HTTP_HOST' => 'www.example.org',
            'HTTPS' => 'on',
            'REMOTE_ADDR' => '193.60.168.69',
            'REMOTE_PORT' => '5390',
            'SCRIPT_NAME' => '/blog/article.php',
            'SCRIPT_FILENAME' => '/path/to/your/server/root/blog/article.php',
            'PHP_SELF' => '/blog/article.php',
        ];

        return [
            'https_request' => [
                'https://www.example.org/blog/article.php?id=10&user=foo',
                $server,
            ],
            'https_request_diff_value' => [
                'https://www.example.org/blog/article.php?id=10&user=foo',
                array_merge($server, ['HTTPS' => '1']),
            ],
            'http_request' => [
                'http://www.example.org/blog/article.php?id=10&user=foo',
                array_merge($server, ['HTTPS' => 'off', 'SERVER_PORT' => '80']),
            ],
            'http_host_missing_fallback_to_server_name' => [
                'https://www.example.org/blog/article.php?id=10&user=foo',
                array_merge($server, ['HTTP_HOST' => null]),
            ],
            'http_host_or_server_name_missing_fallback_to_server_address' => [
                'https://217.112.82.20/blog/article.php?id=10&user=foo',
                array_merge($server, ['HTTP_HOST' => null, 'SERVER_NAME' => null]),
            ],
            'query_string_with_questionmark' => [
                'https://www.example.org/path?continue=https://example.com/path?param=1',
                array_merge(
                    $server,
                    ['REQUEST_URI' => '/path?continue=https://example.com/path?param=1', 'QUERY_STRING' => '']
                ),
            ],
            'no_query_string' => [
                'https://www.example.org/blog/article.php',
                array_merge($server, ['REQUEST_URI' => '/blog/article.php', 'QUERY_STRING' => '']),
            ],
            'host_header_with_port' => [
                'https://www.example.org:8324/blog/article.php?id=10&user=foo',
                array_merge($server, ['HTTP_HOST' => 'www.example.org:8324']),
            ],
            'diff_port' => [
                'https://www.example.org:8324/blog/article.php?id=10&user=foo',
                array_merge($server, ['SERVER_PORT' => '8324']),
            ],
            'missing_query_string' => [
                'https://www.example.org/blog/article.php?id=10&user=foo',
                array_merge($server, ['REQUEST_URI' => '/blog/article.php']),
            ],
        ];
    }

    /* -------------------------------------------------
     * GET HEADERS FROM GLOBALS
     * -------------------------------------------------
     */

    public function testGetHeadersFromGlobals(): void
    {
        $server = [
            'REDIRECT_URL' => '/foo/bar',
            'URL' => '/foo/bar',
            'HTTP_COOKIE' => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_FOO_BAR' => 'FOOBAR',
            'CONTENT_MD5' => 'CONTENT-MD5',
            'CONTENT_LENGTH' => 'UNSPECIFIED',
        ];
        $expected = [
            'cookie' => 'COOKIE',
            'authorization' => 'token',
            'content-type' => 'application/json',
            'accept' => 'application/json',
            'x-foo-bar' => 'FOOBAR',
            'content-md5' => 'CONTENT-MD5',
            'content-length' => 'UNSPECIFIED',
        ];

        self::assertSame($expected, HttpUtils::getHeadersFromGlobals($server));
    }

    /* -------------------------------------------------
     * NORMALIZE FILES
     * -------------------------------------------------
     */

    /**
     * @param array<string, array<int, mixed>> $files
     * @param array<int, mixed>                $expected
     *
     * @dataProvider normalizeFilesDataProvider
     */
    public function testNormalizeFiles(array $files, array $expected): void
    {
        self::assertEquals($expected, HttpUtils::normalizeFiles($files));
    }

    public function testNormalizeFilesWithInvalidFilesThrowsException(): void
    {
        $this->expectException(UploadedFileException::class);

        HttpUtils::normalizeFiles(['invalid']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function normalizeFilesDataProvider(): array
    {
        return [
            'single_file' => [
                [
                    'file' => [
                        'name' => 'MyFile.txt',
                        'type' => 'text/plain',
                        'tmp_name' => '/tmp/php/php1h4j1o',
                        'error' => '0',
                        'size' => '123',
                    ],
                ],
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
            ],
            'empty_file' => [
                [
                    'image_file' => [
                        'name' => '',
                        'type' => '',
                        'tmp_name' => '',
                        'error' => '4',
                        'size' => '0',
                    ],
                ],
                [
                    'image_file' => new UploadedFile(
                        '',
                        0,
                        UPLOAD_ERR_NO_FILE,
                        '',
                        ''
                    ),
                ],
            ],
            'already_converted' => [
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
            ],
        ];
    }

    /* -------------------------------------------------
     * PARSE COOKIE HEADER
     * -------------------------------------------------
     */

    public function testParseCookieHeader(): void
    {
        self::assertEquals(
            ['name' => 'value', 'other' => '123'],
            HttpUtils::parseCookieHeader('name=value; other=123')
        );
    }

    public function testParseCookieHeaderContainingSpecialCharacters(): void
    {
        $cookieHeader = 'name=%20%21%22%23%24%25%26%27%28%29%2A%2B%2C-./0123456789%3A%3B%3C%3D%3E%3F%40ABCDEFGHIJKLMNOPQRSTUVWXYZ%5B%5C%5D%5E_%60abcdefghijklmnopqrstuvwxyz%7B%7C%7D~';
        $expectedResult = ['name' => ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~'];

        self::assertEquals($expectedResult, HttpUtils::parseCookieHeader($cookieHeader));
    }

    public function testParseCookieHeaderWithEmptyHeader(): void
    {
        self::assertEquals([], HttpUtils::parseCookieHeader(''));
    }

    /* -------------------------------------------------
     * INJECT CONTENT TYPE
     * -------------------------------------------------
     */

    public function testInjectContentTypeWhenHeadersContainContentType(): void
    {
        $headers = ['Content-Type' => ['application/json']];

        self::assertEquals($headers, HttpUtils::injectContentType('text/html', $headers));
    }

    public function testInjectContentTypeWhenHeadersDoNotContainContentType(): void
    {
        self::assertEquals(
            ['Authorization' => ['Bearer Token'], 'content-type' => ['application/xml']],
            HttpUtils::injectContentType('application/xml', ['Authorization' => ['Bearer Token']])
        );
    }

    public function testInjectContentTypeWithEmptyHeaders(): void
    {
        self::assertEquals(
            ['content-type' => ['application/json']],
            HttpUtils::injectContentType('application/json', [])
        );
    }
}
