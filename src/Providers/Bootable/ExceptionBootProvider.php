<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Exceptions\Handlers\ExceptionHandler;
use Zaphyr\Framework\Providers\AbstractServiceProvider;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ExceptionBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        ExceptionHandlerInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $exceptionHandler = $this->has(ExceptionHandlerInterface::class)
            ? $this->get(ExceptionHandlerInterface::class)
            : new ExceptionHandler($this->application);

        $exceptionHandler->register();

        if (!$this->application->isTestingEnvironment()) {
            ini_set('display_errors', 'Off');
        }
    }
}
