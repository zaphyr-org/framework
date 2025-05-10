<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Encrypt\Encrypt;
use Zaphyr\Framework\Exceptions\FrameworkException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EncryptionServiceProvider extends AbstractServiceProvider
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
        $this->getContainer()->bindSingleton(EncryptInterface::class, function () {
            $key = $this->sanitizeKey($this->config('app.encryption.key', ''));
            $cipher = $this->config('app.encryption.cipher', 'AES-256-CBC');

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
