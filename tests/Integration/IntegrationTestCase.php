<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration;

use PHPUnit\Framework\TestCase;
use Zaphyr\Container\Container;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Application;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Kernel\HttpKernel;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
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
        $_ENV['ROOT_DIR'] ??= $options['root_dir'] ?? __DIR__ . '/TestAssets';

        $application = new Application($_ENV['ROOT_DIR']);
        $application->setEnvironment($_ENV['APP_ENV']);

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

        return static::$application->getContainer();
    }
}
