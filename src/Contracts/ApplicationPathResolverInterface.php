<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ApplicationPathResolverInterface
{
    /**
     * @param string $path
     *
     * @return string
     */
    public function getRootPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getAppPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getBinPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getConfigPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPublicPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getResourcesPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getStoragePath(string $path = ''): string;
}
