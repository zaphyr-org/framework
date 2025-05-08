<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\App;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'app:key', description: 'Generate an application key')]
class KeyGenerateCommand extends AbstractCommand
{
    /**
     * @param ApplicationInterface $zaphyr
     * @param ConfigInterface      $config
     */
    public function __construct(protected ApplicationInterface $zaphyr, protected ConfigInterface $config)
    {
        parent::__construct($this->zaphyr);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addOption(
            'show',
            's',
            InputOption::VALUE_NONE,
            'Display the generated key instead of writing it to the .env file'
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force the operation to run when in production mode'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $this->generateRandomKey();

        if ($input->getOption('show')) {
            $output->writeln('<info>' . $key . '</info>');

            return self::SUCCESS;
        }

        $confirmed = $this->confirmToProceed(
            $input,
            $output,
            $this->zaphyr->isProductionEnvironment(),
            'Application is in production environment!'
        );

        if ($confirmed) {
            $this->writeNewKeyToEnvFile($key);

            $output->writeln('<info>Application key set successfully.</info>');
        }

        return self::SUCCESS;
    }

    /**
     * @return string
     */
    protected function generateRandomKey(): string
    {
        $cipher = $this->config->get('app.encryption.cipher');
        $key = random_bytes($cipher === 'AES-128-CBC' ? 16 : 32);

        return 'base64:' . base64_encode($key);
    }

    /**
     * @param string $key
     */
    protected function writeNewKeyToEnvFile(string $key): void
    {
        $file = $this->zaphyr->getRootPath('.env');

        $contents = file_get_contents($file);
        $contents = is_string($contents) ? $contents : '';

        file_put_contents(
            $file,
            preg_replace(
                $this->keyReplacementPattern(),
                'APP_KEY=' . $key,
                $contents
            )
        );
    }

    /**
     * @return string
     */
    protected function keyReplacementPattern(): string
    {
        $escaped = preg_quote('=' . $this->config->get('app.encryption.key'), '/');

        return "/^APP_KEY$escaped/m";
    }
}
