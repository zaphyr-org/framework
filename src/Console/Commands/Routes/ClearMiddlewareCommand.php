<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Routes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'routes:middleware:clear', description: 'Clear the middleware cache file')]
class ClearMiddlewareCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        File::delete($this->zaphyr->getMiddlewareCachePath());

        $output->writeln('<info>Middleware cache cleared successfully.</info>');

        return self::SUCCESS;
    }
}
