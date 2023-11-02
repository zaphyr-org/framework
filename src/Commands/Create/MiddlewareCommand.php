<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands\Create;

use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'create:middleware', description: 'Create a new middleware class')]
class MiddlewareCommand extends AbstractCreateCommand
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'middleware';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultNamespace(): string
    {
        return 'App\Middleware';
    }
}
