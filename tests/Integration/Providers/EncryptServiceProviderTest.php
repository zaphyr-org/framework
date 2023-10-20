<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Encrypt\Encrypt;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Framework\Providers\EncryptServiceProvider;
use Zaphyr\FrameworkTests\Integration\IntegrationTestCase;

class EncryptServiceProviderTest extends IntegrationTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var EncryptServiceProvider
     */
    protected EncryptServiceProvider $encryptServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->encryptServiceProvider = new EncryptServiceProvider();
        $this->encryptServiceProvider->setContainer($this->container);
        $this->encryptServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->encryptServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->encryptServiceProvider->provides(EncryptInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'key' => 'aaaaaaaaaaaaaaaa',
            ],
        ]);

        /** @var Encrypt $encrypt */
        $encrypt = $this->container->get(EncryptInterface::class);

        self::assertInstanceOf(Encrypt::class, $encrypt);
    }

    public function testRegisterWith256BitKey(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'key' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'cipher' => 'AES-256-CBC',
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
                'key' => 'base64:' . base64_encode('aaaaaaaaaaaaaaaa'),
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
                'key' => '',
            ],
        ]);

        $this->container->get(EncryptInterface::class);
    }
}
