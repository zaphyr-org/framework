<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands\Create;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'create:provider', description: 'Create a new service provider class')]
class ProviderCommand extends AbstractCreateCommand
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'provider';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultNamespace(): string
    {
        return 'App\Providers';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addOption(
            'bootable',
            'b',
            InputOption::VALUE_NONE,
            'Make the provider bootable'
        );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = trim($input->getArgument('name'));
        $namespace = $this->prepareNamespace($input->getOption('namespace'));

        $stubName = $this->getStubName();

        if ($input->hasOption('bootable') && $input->getOption('bootable')) {
            $stubName .= '-bootable';
        }

        $contents = $this->prepareContents($stubName, $name, $namespace);
        $directory = $this->prepareDestinationDirectory($namespace);
        $file = $this->getDestinationFile($directory, $name);

        if ($this->confirm($input, $output, [$file])) {
            $this->createFiles($output, [$file => $contents]);
        }

        return self::SUCCESS;
    }
}
