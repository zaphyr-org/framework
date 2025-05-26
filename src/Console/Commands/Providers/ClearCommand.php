<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Providers;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'providers:clear', description: 'Clear the service providers cache file')]
class ClearCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        File::delete($this->zaphyr->getProvidersCachePath());

        $output->writeln('<info>Service providers cache cleared successfully.</info>');

        return self::SUCCESS;
    }
}
