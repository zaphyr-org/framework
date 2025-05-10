<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Encrypt\Encrypt;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Framework\Providers\EncryptionServiceProvider;
use Zaphyr\Framework\Testing\HttpTestCase;

class EncryptionServiceProviderTest extends HttpTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var EncryptionServiceProvider
     */
    protected EncryptionServiceProvider $encryptionServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->encryptionServiceProvider = new EncryptionServiceProvider(static::bootApplication());
        $this->encryptionServiceProvider->setContainer($this->container);
        $this->encryptionServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->encryptionServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->encryptionServiceProvider->provides(EncryptInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'encryption' => [
                    'key' => str_repeat('a', 32),
                ]
            ],
        ]);

        /** @var Encrypt $encrypt */
        $encrypt = $this->container->get(EncryptInterface::class);

        self::assertInstanceOf(Encrypt::class, $encrypt);
    }

    public function testRegisterWith128BitKey(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'encryption' => [
                    'key' => str_repeat('a', 16),
                    'cipher' => 'AES-128-CBC',
                ],
            ],
        ]);

        /** @var Encrypt $encrypt */
        $encrypt = $this->container->get(EncryptInterface::class);

        self::assertInstanceOf(Encrypt::class, $encrypt);
    }

    public function testRegisterWithBase64Key(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'encryption' => [
                    'key' => 'base64:' . base64_encode(str_repeat('a', 32)),
                ],
            ],
        ]);

        /** @var Encrypt $encrypt */
        $encrypt = $this->container->get(EncryptInterface::class);

        self::assertInstanceOf(Encrypt::class, $encrypt);
    }

    public function testRegisterThrowsExceptionOnNotExistingKey(): void
    {
        $this->expectException(FrameworkException::class);

        $this->container->get(EncryptInterface::class);
    }

    public function testRegisterThrowsExceptionOnEmptyStringKey(): void
    {
        $this->expectException(FrameworkException::class);

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'encryption' => [
                    'key' => '',
                ],
            ],
        ]);

        $this->container->get(EncryptInterface::class);
    }
}
