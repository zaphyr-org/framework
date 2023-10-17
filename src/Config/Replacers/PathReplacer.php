<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Config\Replacers;

use Zaphyr\Config\Contracts\ReplacerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class PathReplacer implements ReplacerInterface
{
    /**
     * @var array<string, string>
     */
    protected array $pathMethods = [
        'root' => 'getRootPath',
        'config' => 'getConfigPath',
        'public' => 'getPublicPath',
        'resources' => 'getResourcesPath',
        'storage' => 'getStoragePath',
    ];

    /**
     * @param ApplicationInterface $application
     */
    public function __construct(protected ApplicationInterface $application)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws FrameworkException if the path is not valid
     */
    public function replace(string $value): mixed
    {
        if (!isset($this->pathMethods[$value])) {
            throw new FrameworkException('The path "' . $value . '" is not a valid path');
        }

        return $this->application->{$this->pathMethods[$value]}();
    }
}
