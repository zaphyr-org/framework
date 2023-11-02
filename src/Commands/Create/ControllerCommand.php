<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands\Create;

use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'create:controller', description: 'Create a new controller class')]
class ControllerCommand extends AbstractCreateCommand
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'controller';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultNamespace(): string
    {
        return 'App\Controllers';
    }
}
