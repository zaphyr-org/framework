<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Providers\SessionServiceProvider;
use Zaphyr\Framework\Tests\IntegrationTestCase;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Contracts\SessionManagerInterface;
use Zaphyr\Session\SessionManager;

class SessionServiceProviderTest extends IntegrationTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var SessionServiceProvider
     */
    protected SessionServiceProvider $sessionServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->sessionServiceProvider = new SessionServiceProvider();
        $this->sessionServiceProvider->setContainer($this->container);
        $this->sessionServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->sessionServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->sessionServiceProvider->provides(SessionManagerInterface::class));
        self::assertTrue($this->sessionServiceProvider->provides(SessionInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'sessions' => [
                'name' => 'foo',
                'handlers' => [
                    'file' => [
                        'path' => 'sessions',
                        'expire' => 60,
                        'default' => SessionManager::FILE_HANDLER,
                        'encrypt' => true,
                    ],
                ],
            ],
        ]);

        $sessionManager = $this->container->get(SessionManagerInterface::class);
        $session = $this->container->get(SessionInterface::class);

        self::assertInstanceOf(SessionManager::class, $sessionManager);
        self::assertInstanceOf(SessionInterface::class, $session);
    }
}
