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
#[AsCommand(name: 'routes:controllers:clear', description: 'Clear the controllers cache file')]
class ClearControllersCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        File::delete($this->zaphyr->getControllersCachePath());

        $output->writeln('<info>Controllers cache cleared successfully.</info>');

        return self::SUCCESS;
    }
}
