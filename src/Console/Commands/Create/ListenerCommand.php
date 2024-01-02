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
#[AsCommand(name: 'create:listener', description: 'Create a new event listener class')]
class ListenerCommand extends AbstractCreateCommand
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'listener';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultNamespace(): string
    {
        return 'App\Listeners';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'event',
            'e',
            InputOption::VALUE_OPTIONAL,
            'The event class that the listener handles',
            'object'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $stubName = $this->getStubName();
        $namespace = $this->prepareNamespace($input->getOption('namespace'));

        $contents = $this->prepareContents($stubName, $name, $namespace);
        $event = trim($input->getOption('event'));
        $event = '\\' . ltrim($this->prepareNamespace($event), '\\');
        $contents = str_replace('%event%', $event, $contents);

        $directory = $this->prepareDestinationDirectory($namespace);
        $file = $this->getDestinationFile($directory, $name);

        if ($this->confirm($input, $output, [$file])) {
            $this->createFiles($output, [$file => $contents]);
        }

        return self::SUCCESS;
    }
}
