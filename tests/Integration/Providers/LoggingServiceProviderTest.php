<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Psr\Log\LoggerInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Providers\LoggingServiceProvider;
use Zaphyr\Framework\Testing\HttpTestCase;
use Zaphyr\Logger\Contracts\LogManagerInterface;
use Zaphyr\Logger\Handlers\FileHandler;
use Zaphyr\Logger\Handlers\MailHandler;
use Zaphyr\Logger\Handlers\RotateHandler;
use Zaphyr\Logger\Logger;
use Zaphyr\Logger\LogManager;

class LoggingServiceProviderTest extends HttpTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var LoggingServiceProvider
     */
    protected LoggingServiceProvider $loggingServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->loggingServiceProvider = new LoggingServiceProvider();
        $this->loggingServiceProvider->setContainer($this->container);
        $this->loggingServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->loggingServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegisterWithFileHandler(): void
    {
        self::assertTrue($this->loggingServiceProvider->provides(LogManagerInterface::class));
        self::assertTrue($this->loggingServiceProvider->provides(LoggerInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'logging' => [
                    'default_channel' => 'test',
                    'channels' => [
                        'test' => [
                            'handlers' => [
                                'file' => [
                                    'filename' => 'logs/test.log',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
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
            'app' => [
                'logging' => [
                    'default_channel' => 'test',
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
            ],
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
            'app' => [
                'logging' => [
                    'default_channel' => 'test',
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
            ]
        ]);

        /** @var LogManager $logManager */
        $logManager = $this->container->get(LogManagerInterface::class);

        self::assertInstanceOf(LogManager::class, $logManager);
        self::assertInstanceOf(RotateHandler::class, $logManager->logger()->getHandlers()[0]);
    }
}
