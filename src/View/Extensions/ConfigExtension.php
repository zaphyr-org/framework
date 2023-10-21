<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ConfigExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('config_get', [ConfigRuntime::class, 'get']),
        ];
    }
}
