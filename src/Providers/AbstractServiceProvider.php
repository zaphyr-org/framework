<?php

namespace Zaphyr\Framework\Providers;

use Zaphyr\Container\AbstractServiceProvider as BaseServiceProvider;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractServiceProvider extends BaseServiceProvider
{
    /**
     * @param ApplicationInterface $application
     */
    public function __construct(protected ApplicationInterface $application)
    {
    }
}
