<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Routes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCacheCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'routes:middleware:cache', description: 'Create a middleware cache file')]
class CacheMiddlewareCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->callSilent('routes:middleware:clear');

        if ($data = $this->applicationRegistry->middleware()) {
            $this->write($this->zaphyr->getMiddlewareCachePath(), $data);
        }

        $output->writeln('<info>Middleware cached successfully.</info>');

        return self::SUCCESS;
    }
}
