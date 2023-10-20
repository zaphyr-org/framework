<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Encrypt\Encrypt;
use Zaphyr\Framework\Exceptions\FrameworkException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EncryptServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        EncryptInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->getContainer()->bindSingleton(EncryptInterface::class, function ($container) {
            $config = $container->get(ConfigInterface::class);
            $key = $this->sanitizeKey($config->get('app.key', ''));
            $cipher = $config->get('app.cipher', 'AES-128-CBC');

            return new Encrypt($key, $cipher);
        });
    }

    /**
     * @param string $key
     *
     * @throws FrameworkException if no application encryption key has been specified
     * @return string
     */
    protected function sanitizeKey(string $key): string
    {
        if (empty($key)) {
            throw new FrameworkException('No application encryption key has been specified');
        }

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return $key;
    }
}
