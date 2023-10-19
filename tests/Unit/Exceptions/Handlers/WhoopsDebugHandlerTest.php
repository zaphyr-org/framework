<?php

namespace Zaphyr\FrameworkTests\Unit\Exceptions\Handlers;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\RunInterface;
use Zaphyr\Framework\Exceptions\Handlers\WhoopsDebugHandler;

class WhoopsDebugHandlerTest extends TestCase
{
    /**
     * @var RunInterface&MockObject
     */
    protected RunInterface&MockObject $runMock;

    /**
     * @var HandlerInterface&MockObject
     */
    protected HandlerInterface&MockObject $handlerMock;

    /**
     * @var ServerRequestInterface&MockObject
     */
    protected ServerRequestInterface&MockObject $serverRequestMock;

    /**
     * @var WhoopsDebugHandler
     */
    protected WhoopsDebugHandler $whoopsDebugHandler;

    public function setUp(): void
    {
        $this->runMock = $this->createMock(RunInterface::class);
        $this->handlerMock = $this->createMock(HandlerInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);

        $this->whoopsDebugHandler = new WhoopsDebugHandler($this->runMock, $this->handlerMock);
    }

    public function tearDown(): void
    {
        unset($this->runMock, $this->handlerMock, $this->serverRequestMock, $this->whoopsDebugHandler);
    }

    /* -------------------------------------------------
     * RENDER
     * -------------------------------------------------
     */

    public function testRender(): void
    {
        $throwable = new Exception('Whoops');
        $debugOutput = 'Whoops';

        $this->runMock->expects(self::once())
            ->method('pushHandler')
            ->with($this->handlerMock);

        $this->runMock->expects(self::once())
            ->method('writeToOutput')
            ->with(false);

        $this->runMock->expects(self::once())
            ->method('allowQuit')
            ->with(false);

        $this->runMock->expects(self::once())
            ->method('handleException')
            ->with($throwable)
            ->willReturn($debugOutput);


        self::assertEquals($debugOutput, $this->whoopsDebugHandler->render($this->serverRequestMock, $throwable));
    }

    public function testRenderWithPrettyPageHandler(): void
    {
        $throwable = new Exception('Whoops');
        $debugOutput = 'Whoops';
        $blacklist = ['_POST' => ['password']];

        $prettyPageHandlerMock = $this->createMock(PrettyPageHandler::class);

        $prettyPageHandlerMock->expects(self::exactly(2))
            ->method('addDataTable');

        $prettyPageHandlerMock->expects(self::once())
            ->method('handleUnconditionally')
            ->with(true);

        $prettyPageHandlerMock->expects(self::once())
            ->method('blacklist')
            ->with('_POST', 'password');

        $this->runMock->expects(self::once())
            ->method('pushHandler')
            ->with($prettyPageHandlerMock);

        $this->runMock->expects(self::once())
            ->method('writeToOutput')
            ->with(false);

        $this->runMock->expects(self::once())
            ->method('allowQuit')
            ->with(false);

        $this->runMock->expects(self::once())
            ->method('handleException')
            ->with($throwable)
            ->willReturn($debugOutput);

        $this->serverRequestMock->expects(self::once())
            ->method('getHeaders')
            ->willReturn([
                'X-Powered-By' => ['ZAPHYR'],
            ]);

        $whoopsDebugHandler = new WhoopsDebugHandler($this->runMock, $prettyPageHandlerMock, $blacklist);

        self::assertEquals($debugOutput, $whoopsDebugHandler->render($this->serverRequestMock, $throwable));
    }
}
