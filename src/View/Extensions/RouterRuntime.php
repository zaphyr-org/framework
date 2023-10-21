<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Router\Contracts\RouterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RouterRuntime
{
    /**
     * @param RouterInterface $router
     */
    public function __construct(protected RouterInterface $router, protected ServerRequestInterface $request)
    {
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $params
     *
     * @return string
     */
    public function getPathFromName(string $name, array $params = []): string
    {
        return $this->router->getPathFromName($name, $params);
    }

    /**
     * @return string
     */
    public function getCurrentPath(): string
    {
        return $this->request->getUri()->getPath();
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $params
     *
     * @return bool
     */
    public function isCurrentPath(string $name, array $params = []): bool
    {
        return $this->request->getUri()->getPath() === $this->getPathFromName($name, $params);
    }
}
