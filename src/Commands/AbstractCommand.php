<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands;

use Symfony\Component\Console\Command\Command;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class AbstractCommand extends Command
{
    /**
     * @param ApplicationInterface $zaphyr
     */
    public function __construct(protected ApplicationInterface $zaphyr)
    {
        parent::__construct();
    }
}
