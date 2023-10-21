<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RouterExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('route_path', [RouterRuntime::class, 'getPathFromName']),
            new TwigFunction('route_current', [RouterRuntime::class, 'getCurrentPath']),
            new TwigFunction('route_is_current', [RouterRuntime::class, 'isCurrentPath'])
        ];
    }
}
