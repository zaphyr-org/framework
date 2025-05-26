<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands;

use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractCacheCommand extends AbstractCommand
{
    /**
     * @param ApplicationInterface         $zaphyr
     * @param ApplicationRegistryInterface $applicationRegistry
     */
    public function __construct(
        ApplicationInterface $zaphyr,
        protected ApplicationRegistryInterface $applicationRegistry
    ) {
        parent::__construct($zaphyr);
    }

    /**
     * @param string       $path
     * @param array<mixed> $data
     *
     * @return void
     */
    protected function write(string $path, array $data): void
    {
        $this->createMissingCacheDirectory($path);

        File::put($path, '<?php return ' . var_export($data, true) . ';' . PHP_EOL, true);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    protected function createMissingCacheDirectory(string $path): void
    {
        $directory = dirname($path);

        if (!file_exists($directory)) {
            File::createDirectory($directory, 0777, true, true);
        }
    }
}
