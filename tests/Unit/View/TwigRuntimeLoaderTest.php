<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\View;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zaphyr\Framework\View\TwigRuntimeLoader;

class TwigRuntimeLoaderTest extends TestCase
{
    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var TwigRuntimeLoader
     */
    protected TwigRuntimeLoader $twigRuntimeLoader;

    public function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);

        $this->twigRuntimeLoader = new TwigRuntimeLoader($this->containerMock);
    }

    public function tearDown(): void
    {
        unset($this->containerMock, $this->twigRuntimeLoader);
    }

    /* -------------------------------------------------
     * LOAD
     * -------------------------------------------------
     */

    public function testLoad(): void
    {
        $class = 'class';

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with($class)
            ->willReturn($class);

        self::assertSame($class, $this->twigRuntimeLoader->load($class));
    }
}
