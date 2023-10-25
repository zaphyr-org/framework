<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Psr\Http\Message\ServerRequestInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class SessionExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('session_has', [SessionRuntime::class, 'has']),
            new TwigFunction('session_get', [SessionRuntime::class, 'get']),
            new TwigFunction('session_has_input', [SessionRuntime::class, 'hasInput']),
            new TwigFunction('session_get_input', [SessionRuntime::class, 'getInput']),
        ];
    }
}
