<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Testing;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Http\RedirectResponse;
use Zaphyr\Framework\Http\Response;
use Zaphyr\Framework\Providers\EventsServiceProvider;
use Zaphyr\Framework\Providers\LoggingServiceProvider;
use Zaphyr\Framework\Testing\HttpTestCase;
use Zaphyr\HttpMessage\UploadedFile;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Utils\File;

class HttpTestCaseTest extends HttpTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @var string
     */
    protected string $testLogDir = __DIR__ . '/testing';

    /**
     * @var string
     */
    protected string $testSessionDir = __DIR__ . '/sessions';

    protected function setUp(): void
    {
        $this->container = HttpTestCase::getContainer();
        $this->router = $this->container->get(RouterInterface::class);

        mkdir($this->testLogDir, 0777, true);
        mkdir($this->testSessionDir, 0777, true);

        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'encryption' => [
                    'key' => str_repeat('a', 32),
                ],
                'session' => [
                    'default_handler' => 'array',
                ],
                // @todo use null logger as soon as it is implemented
                'logging' => [
                    'default_channel' => 'testing',
                    'channels' => [
                        'testing' => [
                            'handlers' => [
                                'file' => [
                                    'filename' => $this->testLogDir . '/testing.log',
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $this->container->registerServiceProvider(new EventsServiceProvider());
        $this->container->registerServiceProvider(new LoggingServiceProvider());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->container, $this->router);

        File::deleteDirectory($this->testLogDir);
        File::deleteDirectory($this->testSessionDir);
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBootApplication(): void
    {
        self::assertInstanceOf(ApplicationInterface::class, static::bootApplication());
    }

    public function testBootApplicationWithCustomEnvironment(): void
    {
        self::assertTrue(static::bootApplication('custom')->isEnvironment('custom'));
    }

    /* -------------------------------------------------
     * REQUEST
     * -------------------------------------------------
     */

    public function testHttpMethods(): void
    {
        $this->router->get('/call', fn() => new Response());
        $this->router->get('/get', fn() => new Response());
        $this->router->post('/post', fn() => new Response());
        $this->router->put('/put', fn() => new Response());
        $this->router->patch('/patch', fn() => new Response());
        $this->router->delete('/delete', fn() => new Response());
        $this->router->head('/head', fn() => new Response());
        $this->router->options('/options', fn() => new Response());

        self::assertEquals(200, $this->call('GET', '/call')->getStatusCode());
        self::assertEquals(200, $this->get('/get')->getStatusCode());
        self::assertEquals(200, $this->post('/post')->getStatusCode());
        self::assertEquals(200, $this->put('/put')->getStatusCode());
        self::assertEquals(200, $this->patch('/patch')->getStatusCode());
        self::assertEquals(200, $this->delete('/delete')->getStatusCode());
        self::assertEquals(200, $this->head('/head')->getStatusCode());
        self::assertEquals(200, $this->options('/options')->getStatusCode());
    }

    public function testCallWithHeaders(): void
    {
        $this->router->get('/', function ($request) {
            $response = new Response();

            return $response->withHeader('x-header', $request->getHeaderline('foo'));
        });

        $response = $this->call('GET', '/', ['foo' => 'bar']);

        self::assertEquals(['bar'], $response->getHeader('x-header'));
    }

    public function testCallWithServerParams(): void
    {
        $this->router->get('/', function ($request) {
            $response = new Response();

            return $response->withHeader('x-server-params', json_encode($request->getServerParams()));
        });

        $response = $this->call('GET', '/', server: ['foo' => 'bar']);

        self::assertEquals(['{"foo":"bar"}'], $response->getHeader('x-server-params'));
    }

    public function testCallWithCookieParams(): void
    {
        $this->router->get('/', function ($request) {
            $response = new Response();

            return $response->withHeader('x-cookie-params', json_encode($request->getCookieParams()));
        });

        $response = $this->call('GET', '/', cookies: ['foo' => 'bar']);

        self::assertEquals(['{"foo":"bar"}'], $response->getHeader('x-cookie-params'));
    }

    public function testCallWithQueryParams(): void
    {
        $this->router->get('/', function ($request) {
            $response = new Response();

            return $response->withHeader('x-query-params', json_encode($request->getQueryParams()));
        });

        $response = $this->call('GET', '/', query: ['foo' => 'bar']);

        self::assertEquals(['{"foo":"bar"}'], $response->getHeader('x-query-params'));
    }

    public function testCallWithUploadedFiles(): void
    {
        $this->router->get('/', function ($request) {
            $response = new Response();

            foreach ($request->getUploadedFiles() as $key => $file) {
                $response = $response->withHeader(
                    'Content-Disposition',
                    'attachment; filename="' . $file->getClientFilename() . '"'
                );
            }

            return $response;
        });

        $uploadedFile = new UploadedFile('bar', null, 0, 'file', 'mime/type');
        $response = $this->call('GET', '/', files: ['file' => $uploadedFile]);

        self::assertEquals(['attachment; filename="file"'], $response->getHeader('Content-Disposition'));
    }

    /* -------------------------------------------------
     * RESPONSE
     * -------------------------------------------------
     */

    public function testAssertHeader(): void
    {
        $this->router->get('/', fn() => new Response(headers: ['x-foo' => 'bar']));

        self::assertHeader('x-foo', null, $this->get('/'));
    }

    public function testAssertHeaderWithValue(): void
    {
        $this->router->get('/', fn() => new Response(headers: ['x-foo' => 'bar']));

        self::assertHeader('x-foo', 'bar', $this->get('/'));
    }

    public function testAssertNotHasHeader(): void
    {
        $this->router->get('/', fn() => new Response());

        self::assertNotHasHeader('x-foo', $this->get('/'));
    }

    public function testAssertBody(): void
    {
        $response = new Response();
        $response->getBody()->write('foo');

        $this->router->get('/', fn() => $response);

        self::assertBody('foo', $this->get('/'));
    }

    /* -------------------------------------------------
     * STATUS CODES
     * -------------------------------------------------
     */

    public function testAssertStatus(): void
    {
        $this->router->get('/', fn() => new Response());

        self::assertStatus(200, $this->get('/'));
    }

    /**
     * @dataProvider successfulDataProvider
     *
     * @param int $statusCode
     */
    public function testAssertSuccessful(int $statusCode): void
    {
        $this->router->get('/', fn() => new Response(statusCode: $statusCode));

        self::assertSuccessful($this->get('/'));
    }

    /**
     * @return int[][]
     */
    public static function successfulDataProvider(): array
    {
        return [
            [200],
            [201],
            [202],
            [203],
            [204],
            [205],
            [206],
            [207],
            [208],
            [226],
        ];
    }

    /**
     * @dataProvider redirectDataProvider
     *
     * @param int $statusCode
     */
    public function testAssertRedirect(int $statusCode): void
    {
        $this->router->get('/', fn() => new Response(statusCode: $statusCode));

        self::assertRedirect($this->get('/'));
    }

    /**
     * @return int[][]
     */
    public static function redirectDataProvider(): array
    {
        return [
            [300],
            [301],
            [302],
            [303],
            [304],
            [305],
            [307],
            [308],
        ];
    }

    public function testAssertRedirectTo(): void
    {
        $this->router->get('/', fn() => new RedirectResponse('/redirect'));


        self::assertRedirectTo('/redirect', $this->get('/'));
    }

    /**
     * @dataProvider serverErrorDataProvider
     *
     * @param int $statusCode
     */
    public function testAssertServerError(int $statusCode): void
    {
        $this->router->get('/', fn() => new Response(statusCode: $statusCode));

        self::assertServerError($this->get('/'));
    }

    /**
     * @return int[][]
     */
    public static function serverErrorDataProvider(): array
    {
        return [
            [500],
            [501],
            [502],
            [503],
            [504],
            [505],
            [506],
            [507],
            [508],
            [510],
            [511],
        ];
    }

    public function testAssertOk(): void
    {
        $this->router->get('/', fn() => new Response());

        self::assertOk($this->get('/'));
    }

    public function testAssertCreated(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 201));

        self::assertCreated($this->get('/'));
    }

    public function testAssertAccepted(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 202));

        self::assertAccepted($this->get('/'));
    }

    public function testAssertNoContent(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 204));

        self::assertNoContent($this->get('/'));
    }

    public function testAssertMovedPermanently(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 301));

        self::assertMovedPermanently($this->get('/'));
    }

    public function testAssertFound(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 302));

        self::assertFound($this->get('/'));
    }

    public function testAssertSeeOther(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 303));

        self::assertSeeOther($this->get('/'));
    }

    public function testAssertNotModified(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 304));

        self::assertNotModified($this->get('/'));
    }

    public function testAssertTemporaryRedirect(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 307));

        self::assertTemporaryRedirect($this->get('/'));
    }

    public function testAssertPermanentRedirect(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 308));

        self::assertPermanentRedirect($this->get('/'));
    }

    public function testAssertBadRequest(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 400));

        self::assertBadRequest($this->get('/'));
    }

    public function testAssertUnauthorized(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 401));

        self::assertUnauthorized($this->get('/'));
    }

    public function testAssertPaymentRequired(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 402));

        self::assertPaymentRequired($this->get('/'));
    }

    public function testAssertForbidden(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 403));

        self::assertForbidden($this->get('/'));
    }

    public function testAssertNotFound(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 404));

        self::assertNotFound($this->get('/'));
    }

    public function testAssertMethodNotAllowed(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 405));

        self::assertMethodNotAllowed($this->get('/'));
    }

    public function testAssertNotAcceptable(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 406));

        self::assertNotAcceptable($this->get('/'));
    }

    public function testAssertRequestTimeout(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 408));

        self::assertRequestTimeout($this->get('/'));
    }

    public function testAssertConflict(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 409));

        self::assertConflict($this->get('/'));
    }

    public function testAssertGone(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 410));

        self::assertGone($this->get('/'));
    }

    public function testAssertUnsupportedMediaType(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 415));

        self::assertUnsupportedMediaType($this->get('/'));
    }

    public function testAssertUnprocessableEntity(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 422));

        self::assertUnprocessableEntity($this->get('/'));
    }

    public function testAssertTooManyRequests(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 429));

        self::assertTooManyRequests($this->get('/'));
    }

    public function testAssertInternalServerError(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 500));

        self::assertInternalServerError($this->get('/'));
    }

    public function testAssertServiceUnavailable(): void
    {
        $this->router->get('/', fn() => new Response(statusCode: 503));

        self::assertServiceUnavailable($this->get('/'));
    }

    /* -------------------------------------------------
     * GET CONTAINER
     * -------------------------------------------------
     */

    public function testGetContainerReturnsContainerInstance(): void
    {
        self::assertInstanceOf(ContainerInterface::class, static::getContainer());
    }

    public function testGetContainerReturnsSameContainerInstance(): void
    {
        self::assertSame(static::getContainer(), static::getContainer());
    }
}
