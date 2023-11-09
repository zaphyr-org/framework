<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ExceptionBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @param ApplicationInterface $application
     */
    public function __construct(protected ApplicationInterface $application)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $this->getContainer()->get(ExceptionHandlerInterface::class)->register();

        if (!$this->application->isTestingEnvironment()) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //
    }
}
