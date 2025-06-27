<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Routes;

use PHPUnit\Framework\MockObject\MockObject;
use Zaphyr\Framework\Console\Commands\Routes\ListCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;
use Zaphyr\Router\Attributes\Route;
use Zaphyr\Router\Contracts\RouterInterface;

class ListCommandTest extends ConsoleTestCase
{
    protected RouterInterface&MockObject $routerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routerMock = $this->createMock(RouterInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->routerMock);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $route = new Route('/foo', ['GET', 'POST'], 'foo', ['foo'], 'http', 'localhost', 8080);
        $route->setCallable('FooController@foo');

        $this->routerMock->expects(self::once())
            ->method('getRoutes')
            ->willReturn([$route]);

        $command = $this->execute(new ListCommand($this->applicationMock, $this->routerMock));

        self::assertDisplayContains('Path: /foo', $command);
        self::assertDisplayContains('Methods: GET | POST', $command);
        self::assertDisplayContains('Callable: FooController@foo', $command);
        self::assertDisplayContains('Name: foo', $command);
        self::assertDisplayContains('Scheme: http', $command);
        self::assertDisplayContains('Host: localhost', $command);
        self::assertDisplayContains('Port: 8080', $command);
        self::assertDisplayContains("Middleware: foo", $command);
    }

    public function testExecuteWithoutMiddlewareAndStandardPort(): void
    {
        $route = new Route('/bar', ['GET'], 'bar', [], 'https', 'example.com', 443);
        $route->setCallable('BarController@bar');

        $this->routerMock->expects(self::once())
            ->method('getRoutes')
            ->willReturn([$route]);

        $command = $this->execute(new ListCommand($this->applicationMock, $this->routerMock));

        self::assertDisplayContains('Path: /bar', $command);
        self::assertDisplayContains('Methods: GET', $command);
        self::assertDisplayContains('Callable: BarController@bar', $command);
        self::assertDisplayContains('Name: bar', $command);
        self::assertDisplayContains('Scheme: https', $command);
        self::assertDisplayContains('Host: example.com', $command);
        self::assertDisplayContains('Port: ', $command);
        self::assertDisplayContains("Middleware: ", $command);
    }
}
