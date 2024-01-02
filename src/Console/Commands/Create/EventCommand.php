<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Create;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'create:event', description: 'Create a new event class')]
class EventCommand extends AbstractCreateCommand
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'event';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultNamespace(): string
    {
        return 'App\Events';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'stoppable',
            's',
            InputOption::VALUE_NONE,
            'Make the event stoppable'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $stubName = $this->getStubName();

        if ($input->hasOption('stoppable') && $input->getOption('stoppable')) {
            $stubName .= '-stoppable';
        }

        $namespace = $this->prepareNamespace($input->getOption('namespace'));
        $contents = $this->prepareContents($stubName, $name, $namespace);
        $directory = $this->prepareDestinationDirectory($namespace);
        $file = $this->getDestinationFile($directory, $name);

        if ($this->confirm($input, $output, [$file])) {
            $this->createFiles($output, [$file => $contents]);
        }

        return self::SUCCESS;
    }
}
