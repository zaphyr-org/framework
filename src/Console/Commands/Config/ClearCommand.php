<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Config;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'config:clear', description: 'Clear the configuration cache file')]
class ClearCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        File::delete($this->zaphyr->getStoragePath('cache' . DIRECTORY_SEPARATOR . 'config.cache'));

        $output->writeln('<info>Configuration cache cleared successfully.</info>');

        return self::SUCCESS;
    }
}
