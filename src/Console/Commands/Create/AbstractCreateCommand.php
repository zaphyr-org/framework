<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Create;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractCreateCommand extends AbstractCommand
{
    /**
     * @return string
     */
    abstract public function getStubName(): string;

    /**
     * @return string
     */
    abstract public function getDefaultNamespace(): string;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the ' . $this->getStubName() . ' class'
        );

        $this->addOption(
            'namespace',
            'N',
            InputOption::VALUE_OPTIONAL,
            'The namespace of the ' . $this->getStubName() . ' class',
            $this->getDefaultNamespace()
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Overwrite the ' . $this->getStubName() . ' if it already exists'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $namespace = $this->prepareNamespace($input->getOption('namespace'));
        $contents = $this->prepareContents($this->getStubName(), $name, $namespace);
        $directory = $this->prepareDestinationDirectory($namespace);
        $file = $this->getDestinationFile($directory, $name);

        if ($this->confirm($input, $output, [$file])) {
            $this->createFiles($output, [$file => $contents]);
        }

        return self::SUCCESS;
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    protected function prepareNamespace(string $namespace): string
    {
        return trim(str_replace(['\\', '\\\\', '/', '//'], '\\', $namespace));
    }

    /**
     * @param string $stub
     * @param string $name
     * @param string $namespace
     *
     * @return string
     */
    protected function prepareContents(string $stub, string $name, string $namespace): string
    {
        $contents = $this->getStubContent($stub);

        return str_replace(['%class%', '%namespace%'], [trim($name), $namespace], $contents);
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    protected function prepareDestinationDirectory(string $namespace): string
    {
        $directory = $this->getDestinationDirectory($namespace);

        if (!is_dir($directory)) {
            File::createDirectory($directory, recursive: true);
        }

        return $directory;
    }

    /**
     * @param string $name
     *
     * @throws FrameworkException If the stub could not be read
     * @return string
     */
    protected function getStubContent(string $name): string
    {
        $contents = file_get_contents($this->getStubFile($name));

        if ($contents === false) {
            throw new FrameworkException('Could not read stub "' . $name . '"');
        }

        return $contents;
    }

    /**
     * @param string $name
     *
     * @throws FrameworkException If the stub could not be found
     * @return string
     */
    protected function getStubFile(string $name): string
    {
        $stub = dirname(__DIR__, 4) . '/stubs/' . $name . '.stub';

        if (!file_exists($stub)) {
            throw new FrameworkException('Stub "' . $name . '" not found');
        }

        return $stub;
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    protected function getDestinationDirectory(string $namespace): string
    {
        $namespace = explode('\\', $namespace);
        array_shift($namespace);
        $namespace = implode('/', $namespace);

        return $this->zaphyr->getAppPath($namespace);
    }

    /**
     * @param string $directory
     * @param string $name
     * @param string $extension
     *
     * @return string
     */
    protected function getDestinationFile(string $directory, string $name, string $extension = 'php'): string
    {
        return $directory . '/' . $name . '.' . ltrim($extension, '.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string[]        $files
     *
     * @return bool
     */
    protected function confirm(InputInterface $input, OutputInterface $output, array $files): bool
    {
        $shouldConfirm = false;

        foreach ($files as $file) {
            if (file_exists($file)) {
                $shouldConfirm = true;
                break;
            }
        }

        return $this->confirmToProceed(
            $input,
            $output,
            $shouldConfirm,
            'A ' . $this->getStubName() . ' with this name already exists'
        );
    }

    /**
     * @param OutputInterface       $output
     * @param array<string, string> $files
     *
     * @return void
     */
    protected function createFiles(OutputInterface $output, array $files): void
    {
        foreach ($files as $file => $contents) {
            file_put_contents($file, $contents);
        }

        $output->writeln('<info>' . ucfirst($this->getStubName()) . ' created successfully.</info>');
    }
}
