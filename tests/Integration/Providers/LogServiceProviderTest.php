<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Psr\Log\LoggerInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Providers\LogServiceProvider;
use Zaphyr\FrameworkTests\Integration\IntegrationTestCase;
use Zaphyr\Logger\Contracts\LogManagerInterface;
use Zaphyr\Logger\Handlers\FileHandler;
use Zaphyr\Logger\Handlers\MailHandler;
use Zaphyr\Logger\Handlers\RotateHandler;
use Zaphyr\Logger\Logger;
use Zaphyr\Logger\LogManager;

class LogServiceProviderTest extends IntegrationTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var LogServiceProvider
     */
    protected LogServiceProvider $logServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->logServiceProvider = new LogServiceProvider();
        $this->logServiceProvider->setContainer($this->container);
        $this->logServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->logServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegisterWithFileHandler(): void
    {
        self::assertTrue($this->logServiceProvider->provides(LogManagerInterface::class));
        self::assertTrue($this->logServiceProvider->provides(LoggerInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'logging' => [
                'default' => 'test',
                'channels' => [
                    'test' => [
                        'handlers' => [
                            'file' => [
                                'filename' => 'logs/test.log',
                            ],
                        ],
                    ],
                ],
            ]
        ]);

        /** @var LogManager $logManager */
        $logManager = $this->container->get(LogManagerInterface::class);
        /** @var Logger $logger */
        $logger = $this->container->get(LoggerInterface::class);

        self::assertInstanceOf(LogManager::class, $logManager);
        self::assertInstanceOf(LoggerInterface::class, $logger);
        self::assertInstanceOf(FileHandler::class, $logger->getHandlers()[0]);
        self::assertSame($logger, $logManager->logger('test'));
    }

    public function testRegisterWithMailHandler(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'logging' => [
                'default' => 'test',
                'channels' => [
                    'test' => [
                        'handlers' => [
                            'mail' => [
                                'dsn' => 'null://null',
                                'from' => 'from@example.com',
                                'to' => 'to@example',
                                'subject' => 'Whoops'
                            ],
                        ],
                    ],
                ],
            ]
        ]);

        /** @var LogManager $logManager */
        $logManager = $this->container->get(LogManagerInterface::class);

        self::assertInstanceOf(LogManager::class, $logManager);
        self::assertInstanceOf(MailHandler::class, $logManager->logger()->getHandlers()[0]);
    }

    public function testRegisterWithRotateHandler(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'logging' => [
                'default' => 'test',
                'channels' => [
                    'test' => [
                        'handlers' => [
                            'rotate' => [
                                'directory' => 'logs',
                                'interval' => 'day'
                            ],
                        ],
                    ],
                ],
            ]
        ]);

        /** @var LogManager $logManager */
        $logManager = $this->container->get(LogManagerInterface::class);

        self::assertInstanceOf(LogManager::class, $logManager);
        self::assertInstanceOf(RotateHandler::class, $logManager->logger()->getHandlers()[0]);
    }
}