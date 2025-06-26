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
#[AsCommand(name: 'create:controller', description: 'Create a new controller class')]
class ControllerCommand extends AbstractCreateCommand
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'controller';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultNamespace(): string
    {
        return 'App\Controllers';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'single',
            's',
            InputOption::VALUE_NONE,
            'Create a single action controller'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $stubName = $this->getStubName();

        if ($input->hasOption('single') && $input->getOption('single')) {
            $stubName .= '-single';
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
