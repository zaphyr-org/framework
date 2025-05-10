<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Providers\Bootable\ExceptionBootProvider;

class ExceptionBootProviderTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var ExceptionHandlerInterface&MockObject
     */
    protected ExceptionHandlerInterface&MockObject $exceptionHandlerMock;

    /**
     * @var ExceptionBootProvider
     */
    protected ExceptionBootProvider $exceptionBootProvider;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->exceptionHandlerMock = $this->createMock(ExceptionHandlerInterface::class);

        $this->exceptionBootProvider = new ExceptionBootProvider($this->applicationMock);
        $this->exceptionBootProvider->setContainer($this->containerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->containerMock,
            $this->exceptionHandlerMock,
            $this->exceptionBootProvider
        );
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBoot(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ExceptionHandlerInterface::class)
            ->willReturn($this->exceptionHandlerMock);

        $this->exceptionHandlerMock->expects(self::once())
            ->method('register');

        $this->exceptionBootProvider->boot();
    }
}
