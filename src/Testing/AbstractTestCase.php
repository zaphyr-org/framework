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

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var ApplicationInterface|null
     */
    protected static ApplicationInterface|null $application = null;

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
    public static function bootApplication(string|null $environment = null): ApplicationInterface
    {
        static::$application = new Application($_ENV['ROOT_DIR']);
        static::$application->getContainer()->bindSingleton(
            ExceptionHandlerInterface::class,
            $_ENV['EXCEPTION_HANDLER'] ?? ExceptionHandler::class
        );

        $kernel = static::getKernel();
        (new $kernel(static::$application))->bootstrap();

        static::$application->setEnvironment($environment ?? $_ENV['APP_ENV'] ?? 'testing');

        return static::$application;
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
