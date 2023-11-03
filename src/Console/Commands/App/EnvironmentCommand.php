<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\App;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'app:environment', description: 'Display the application environment')]
class EnvironmentCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $environment = $this->zaphyr->getEnvironment();

        $output->writeln('<info>Current application environment: <options=bold>' . $environment . '</></info>');

        return self::SUCCESS;
    }
}
