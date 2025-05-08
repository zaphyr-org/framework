<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands;

use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractClearCommand extends AbstractCommand
{
    /**
     * @param string $directory
     *
     * @return void
     */
    public function clearDirectory(string $directory): void
    {
        $files = File::allFiles($directory);
        $directories = File::directories($directory);

        if ($files !== null) {
            foreach ($files as $file) {
                File::delete($file->getPathname());
            }
        }

        if ($directories !== null) {
            foreach ($directories as $deleteDirectory) {
                File::deleteDirectory($deleteDirectory->getPathname());
            }
        }
    }
}
