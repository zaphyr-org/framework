<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Cache;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(
    name: 'cache:optimize',
    description: 'Warm up and optimize the cache for improved performance.'
)]
class OptimizeCommand extends AbstractCommand
{
    /**
     * @var array<string, string>
     */
    protected array $commands = [
        'console commands' => 'commands:cache',
        'config' => 'config:cache',
        'event listeners' => 'events:cache',
        'service providers' => 'providers:cache',
        'routes controllers' => 'routes:controllers:cache',
        'routes middleware' => 'routes:middleware:cache',
    ];

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->commands as $name => $command) {
            $this->callSilent($command);

            $output->writeln("<comment>- Cache $name...</comment>");
        }

        $output->writeln("<info>Cache files optimized successfully.</info>");

        return self::SUCCESS;
    }
}
