<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands\Cache;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Commands\AbstractCommand;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'cache:clear', description: 'Clear all cache files')]
class ClearCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        File::cleanDirectory($this->zaphyr->getStoragePath('cache'));

        $output->writeln('<info>Cache files cleared successfully.</info>');

        return self::SUCCESS;
    }
}
