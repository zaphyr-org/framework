<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Http\Request;
use Zaphyr\HttpMessage\Stream;
use Zaphyr\HttpMessage\UploadedFile;
use Zaphyr\HttpMessage\Uri;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    protected Request $request;

    protected function setUp(): void
    {
        $this->request = new Request();
    }

    protected function tearDown(): void
    {
        unset($this->request);
    }

    /* -------------------------------------------------
     * FROM GLOBALS
     * -------------------------------------------------
     */

    public function testFromGlobals(): void
    {
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'www.example.com',
            'REQUEST_URI' => '/blog/article.php?id=10&user=foo',
            'CONTENT_TYPE' => 'text/plain',
            'HTTP_ACCEPT' => 'text/html',
            'HTTP_REFERRER' => 'https://example.com',
            'HTTP_USER_AGENT' => 'My User Agent',
            'SERVER_PROTOCOL' => 'HTTP/1.0',
        ];

        $query = [
            'id' => 10,
            'user' => 'foo',
        ];

        $body = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ];

        $cookies = [
            'logged-in' => 'yes!',
        ];

        $files = [
            'file' => [
                'name' => 'MyFile.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/php/php1h4j1o',
                'error' => UPLOAD_ERR_OK,
                'size' => 123,
            ],
        ];

        $request = Request::fromGlobals($server, $query, $body, $cookies, $files);

        self::assertEquals($server['REQUEST_METHOD'], $request->getMethod());
        self::assertEquals(
            new Uri('https://' . $server['HTTP_HOST'] . $server['REQUEST_URI']),
            $request->getUri()
        );
        self::assertEquals('', $request->getBody()->__toString());
        self::assertEquals([
            'host' => [$server['HTTP_HOST']],
            'content-type' => [$server['CONTENT_TYPE']],
            'accept' => [$server['HTTP_ACCEPT']],
            'referrer' => [$server['HTTP_REFERRER']],
            'user-agent' => [$server['HTTP_USER_AGENT']],
        ], $request->getHeaders());
        self::assertEquals('1.0', $request->getProtocolVersion());
        self::assertEquals($server, $request->getServerParams());
        self::assertEquals($cookies, $request->getCookieParams());
        self::assertEquals($body, $request->getParsedBody());
        self::assertEquals($query, $request->getQueryParams());

        $expectedFiles = [
            'file' => new UploadedFile(
                $files['file']['tmp_name'],
                $files['file']['size'],
                $files['file']['error'],
                $files['file']['name'],
                $files['file']['type'],
            ),
        ];

        self::assertEquals($expectedFiles, $request->getUploadedFiles());
    }

    public function testFromGlobalsWithStandardProtocol(): void
    {
        self::assertEquals('1.1', Request::fromGlobals()->getProtocolVersion());
    }

    public function testFromGlobalsWithCookieHeader(): void
    {
        $server = [
            'HTTP_COOKIE' => 'foo=bar; bar=baz',
        ];

        $request = Request::fromGlobals($server);

        self::assertEquals([
            'foo' => 'bar',
            'bar' => 'baz',
        ], $request->getCookieParams());
    }

    /* -------------------------------------------------
     * PARAMS
     * -------------------------------------------------
     */

    public function testGetServerParam(): void
    {
        $key =  'SERVER_NAME';
        $value = 'localhost';

        self::assertNull($this->request->getServerParam($key));
        self::assertEquals($value, $this->request->getServerParam($key, $value));

        $request = new Request(serverParams: [$key => $value]);
        self::assertEquals($value, $request->getServerParam($key));
    }

    public function testGetCookieParam(): void
    {
        $key = 'foo';
        $value = 'bar';

        self::assertNull($this->request->getCookieParam($key));
        self::assertEquals($value, $this->request->getCookieParam($key, $value));

        $request = new Request(cookieParams: [$key => $value]);
        self::assertEquals($value, $request->getCookieParam($key));
    }

    public function testGetQueryParam(): void
    {
        $key = 'foo';
        $value = 'bar';

        self::assertNull($this->request->getQueryParam($key));
        self::assertEquals($value, $this->request->getQueryParam($key, $value));

        $request = new Request(queryParams: [$key => $value]);
        self::assertEquals($value, $request->getQueryParam($key));
    }

    public function testGetParsedBodyParam(): void
    {
        $key = 'foo';
        $value = 'bar';

        self::assertNull($this->request->getParsedBodyParam($key));
        self::assertEquals($value, $this->request->getParsedBodyParam($key, $value));

        self::assertEquals($value, $this->request->withParsedBody([$key => $value])->getParsedBodyParam($key));

        $stream = new Stream('php://temp', 'rw');
        $stream->write($key . '=' . $value);

        self::assertEquals($value, $this->request->withParsedBody($stream)->getParsedBodyParam($key));
    }

    public function testGetParamsWithGetParams(): void
    {
        $params = ['foo' => 'bar'];
        $request = $this->request->withQueryParams($params);

        self::assertEquals($params, $request->getParams());
        self::assertEquals($params, $request->getParams(['foo']));
        self::assertEmpty($request->getParams(['nope']));
    }

    public function testGetParamsWithPostParams(): void
    {
        $params = ['foo' => 'bar'];
        $request = $this->request->withParsedBody($params);

        self::assertEquals($params, $request->getParams());
        self::assertEquals($params, $request->getParams(['foo']));
        self::assertEmpty($request->getParams(['nope']));

        $stream = new Stream('php://temp', 'rw');
        $stream->write('foo=bar');
        $request = $this->request->withParsedBody($stream);

        self::assertEquals($params, $request->getParams());
    }

    public function testGetParamsWithGetAndPostParams(): void
    {
        $params1 = ['foo' => 'bar'];
        $params2 = ['bas' => 'qux'];

        $request = $this->request->withQueryParams($params1);
        $request = $request->withParsedBody($params2);

        self::assertEquals($params1 + $params2, $request->getParams());
        self::assertEquals($params1, $request->getParams(['foo']));
        self::assertEquals($params2, $request->getParams(['bas']));
    }

    public function testGetParamWithGetParams(): void
    {
        $key = 'foo';
        $value = 'bar';
        $request = $this->request->withQueryParams([$key => $value]);

        self::assertNull($request->getParam('nope'));
        self::assertEquals($value, $request->getParam($key));
        self::assertEquals($value, $request->getParam('nope', $value));
    }

    public function testGetParamWithPostParams(): void
    {
        $key = 'foo';
        $value = 'bar';
        $request = $this->request->withParsedBody([$key => $value]);

        self::assertNull($request->getParam('nope'));
        self::assertEquals($value, $request->getParam($key));
        self::assertEquals($value, $request->getParam('nope', $value));
    }

    /* -------------------------------------------------
     * METHODS
     * -------------------------------------------------
     */

    public function testIsMethod(): void
    {
        self::assertTrue($this->request->isMethod('GET'));
    }

    public function testIsGet(): void
    {
        self::assertTrue($this->request->withMethod('GET')->isGet());
    }

    public function testIsPost(): void
    {
        self::assertTrue($this->request->withMethod('POST')->isPost());
    }

    public function testIsPut(): void
    {
        self::assertTrue($this->request->withMethod('PUT')->isPut());
    }

    public function testIsPatch(): void
    {
        self::assertTrue($this->request->withMethod('PATCH')->isPatch());
    }

    public function testIsDelete(): void
    {
        self::assertTrue($this->request->withMethod('DELETE')->isDelete());
    }

    public function testIsHead(): void
    {
        self::assertTrue($this->request->withMethod('HEAD')->isHead());
    }

    public function testIsOptions(): void
    {
        self::assertTrue($this->request->withMethod('OPTIONS')->isOptions());
    }

    public function testIsXhr(): void
    {
        self::assertTrue($this->request->withHeader('X-Requested-With', 'XMLHttpRequest')->isXhr());
    }

    /* -------------------------------------------------
     * CONTENT
     * -------------------------------------------------
     */

    public function testGetContentType(): void
    {
        self::assertNull($this->request->getContentType());

        $contentType = 'application/json';
        $request = $this->request->withHeader('Content-Type', $contentType);

        self::assertEquals($contentType, $request->getContentType());
    }

    public function testGetContentCharset(): void
    {
        self::assertNull($this->request->getContentCharset());

        $contentType = 'application/json; charset=utf-8';
        $request = $this->request->withHeader('Content-Type', $contentType);

        self::assertEquals('utf-8', $request->getContentCharset());
    }

    public function testGetContentLength(): void
    {
        self::assertNull($this->request->getContentLength());

        $contentLength = 123;
        $request = $this->request->withHeader('Content-Length', (string)$contentLength);

        self::assertEquals($contentLength, $request->getContentLength());
    }

    /* -------------------------------------------------
     * MEDIA
     * -------------------------------------------------
     */

    public function testGetMediaType(): void
    {
        self::assertNull($this->request->getMediaType());

        $request = $this->request->withHeader('Content-Type', 'application/json;charset=utf8');

        self::assertEquals('application/json', $request->getMediaType());
    }

    public function testMediaTypeParams(): void
    {
        self::assertEmpty($this->request->getMediaTypeParams());

        $request = $this->request->withHeader('Content-Type', 'application/json;charset=utf8;foo=bar');

        self::assertEquals(['charset' => 'utf8', 'foo' => 'bar'], $request->getMediaTypeParams());
    }
}
