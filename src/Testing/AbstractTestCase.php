<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Testing;

use PHPUnit\Framework\TestCase;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Application;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\Framework\Exceptions\Handlers\ExceptionHandler;
use Zaphyr\Framework\Kernel\ConsoleKernel;
use Zaphyr\Framework\Kernel\HttpKernel;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;
use Zaphyr\HttpEmitter\SapiEmitter;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var ApplicationInterface|null
     */
    protected static ?ApplicationInterface $application = null;

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        static::$application = null;
    }

    /**
     * @return class-string<HttpKernelInterface|ConsoleKernelInterface>
     */
    abstract protected static function getKernel(): string;

    /**
     * @param string|null $environment
     *
     * @return ApplicationInterface
     */
    public static function bootApplication(?string $environment = null): ApplicationInterface
    {
        static::$application = new Application();
        static::bindImportantInterfaces();

        $kernel = static::getKernel();
        (new $kernel(static::$application))->bootstrap();

        static::$application->setEnvironment($environment ?? $_ENV['APP_ENV'] ?? 'testing');

        return static::$application;
    }

    protected static function bindImportantInterfaces(): void
    {
        static::$application?->getContainer()
            ->bindSingleton(HttpKernelInterface::class, $_ENV['HTTP_KERNEL'] ?? HttpKernel::class)
            ->bindSingleton(ConsoleKernelInterface::class, $_ENV['CONSOLE_KERNEL'] ?? ConsoleKernel::class)
            ->bindSingleton(ExceptionHandlerInterface::class, $_ENV['EXCEPTION_HANDLER'] ?? ExceptionHandler::class)
            ->bindSingleton(EmitterInterface::class, $_ENV['EMITTER'] ?? SapiEmitter::class);
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        if (static::$application === null) {
            self::bootApplication();
        }

        /** @var ApplicationInterface $application */
        $application = static::$application;

        return $application->getContainer();
    }
}
