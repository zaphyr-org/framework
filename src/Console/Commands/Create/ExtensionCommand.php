<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Create;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'create:extension', description: 'Create a new view extension class')]
class ExtensionCommand extends AbstractCreateCommand
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultNamespace(): string
    {
        return 'App\Views\Extensions';
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $stubName = $this->getStubName();

        if ($name === null) {
            $output->writeln('<error>Missing required ' . $stubName . ' name argument</error>');

            return self::FAILURE;
        }

        $namespace = $this->prepareNamespace($input->getOption('namespace'));

        $extensionContents = $this->prepareContents($stubName, $name, $namespace);
        $runtimeContents = $this->prepareContents($stubName . '-runtime', $name, $namespace);

        $directory = $this->prepareDestinationDirectory($namespace);

        $extensionFile = $this->getDestinationFile($directory, $name);
        $runtimeFile = $this->getDestinationFile($directory, $name . 'Runtime');

        if ($this->confirm($input, $output, [$extensionFile, $runtimeFile])) {
            $this->createFiles($output, [
                $extensionFile => $extensionContents,
                $runtimeFile => $runtimeContents,
            ]);
        }

        return self::SUCCESS;
    }
}
