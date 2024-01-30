<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Testing;

use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Kernel\ConsoleKernel;
use Zaphyr\Framework\Testing\AbstractTestCase;

class AbstractTestCaseTest extends AbstractTestCase
{
    protected static function getKernel(): string
    {
        return ConsoleKernel::class;
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
