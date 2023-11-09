<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration;

use PHPUnit\Framework\TestCase;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Application;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Exceptions\Handlers\ExceptionHandler;
use Zaphyr\Framework\Kernel\HttpKernel;

class IntegrationTestCase extends TestCase
{
    /**
     * @var ApplicationInterface|null
     */
    protected static ApplicationInterface|null $application = null;

    /**
     * @var bool
     */
    protected static bool $isBooted = false;

    protected function tearDown(): void
    {
        static::$application = null;
        static::$isBooted = false;
    }

    /**
     * @param array<string, string> $options
     *
     * @return ApplicationInterface
     */
    protected static function bootApplication(array $options = []): ApplicationInterface
    {
        $_ENV['APP_ENV'] ??= $options['environment'] ?? 'testing';
        $_ENV['EXCEPTION_HANDLER'] ??= $options['exception_handler'] ?? ExceptionHandler::class;
        $_ENV['ROOT_DIR'] ??= $options['root_dir'];

        $application = new Application($_ENV['ROOT_DIR']);
        $application->setEnvironment($_ENV['APP_ENV']);

        $container = $application->getContainer();

        if (!$container->has(ExceptionHandlerInterface::class)) {
            $container->bindSingleton(ExceptionHandlerInterface::class, $_ENV['EXCEPTION_HANDLER']);
        }

        $httpKernel = new HttpKernel($application);
        $httpKernel->bootstrap();

        static::$application = $application;
        static::$isBooted = true;

        return static::$application;
    }

    /**
     * @return ContainerInterface
     */
    protected static function getContainer(): ContainerInterface
    {
        if (!static::$isBooted) {
            self::bootApplication();
        }

        /** @var ApplicationInterface $application */
        $application = static::$application;

        return $application->getContainer();
    }
}
