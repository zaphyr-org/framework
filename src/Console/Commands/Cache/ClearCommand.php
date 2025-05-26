<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Cache;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(
    name: 'cache:clear',
    description: 'Remove cache files to ensure clean bootstrapping.'
)]
class ClearCommand extends AbstractCommand
{
    /**
     * @var array<string, string>
     */
    protected array $commands = [
        'console commands' => 'commands:clear',
        'config' => 'config:clear',
        'event listeners' => 'events:clear',
        'service providers' => 'providers:clear',
        'routes controllers' => 'routes:controllers:clear',
        'routes middleware' => 'routes:middleware:clear',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addOption(
            'all',
            'a',
            InputOption::VALUE_NONE,
            'Remove the entire cache directory'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('all')) {
            File::deleteDirectory($this->zaphyr->getStoragePath('cache'));
        } else {
            foreach ($this->commands as $name => $command) {
                $this->callSilent($command);

                $output->writeln("<comment>- Delete cached $name...</comment>");
            }
        }

        $output->writeln('<info>Cache files cleared successfully.</info>');

        return self::SUCCESS;
    }
}
