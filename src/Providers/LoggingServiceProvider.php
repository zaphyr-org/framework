<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Contracts\LogManagerInterface;
use Zaphyr\Logger\Formatters\HtmlFormatter;
use Zaphyr\Logger\Handlers\FileHandler;
use Zaphyr\Logger\Handlers\MailHandler;
use Zaphyr\Logger\Handlers\NoopHandler;
use Zaphyr\Logger\Handlers\RotateHandler;
use Zaphyr\Logger\LogManager;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class LoggingServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        LogManagerInterface::class,
        LoggerInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->registerLogManager();
        $this->registerDefaultLogger();
    }

    /**
     * @return void
     */
    protected function registerLogManager(): void
    {
        $this->getContainer()->bindSingleton(LogManagerInterface::class, function ($container) {
            $config = $container->get(ConfigInterface::class);
            $logHandlers = $this->getLogHandlersFromConfig($config);

            return new LogManager($config->get('app.logging.default_channel', ''), $logHandlers);
        });
    }

    /**
     * @return void
     */
    protected function registerDefaultLogger(): void
    {
        $this->getContainer()->bindSingleton(LoggerInterface::class, function ($container) {
            return $container->get(LogManagerInterface::class)->logger();
        });
    }

    /**
     * @param ConfigInterface $config
     *
     * @throws FrameworkException if the log handler is invalid
     * @return array<string, HandlerInterface[]>
     */
    protected function getLogHandlersFromConfig(ConfigInterface $config): array
    {
        $logHandlers = [];

        foreach ($config->get('app.logging.channels', []) as $name => $channel) {
            foreach ($channel['handlers'] as $handler => $handlerConfig) {
                $logHandlers[$name][] = match ($handler) {
                    'file' => $this->prepareFileHandlerInstance($name, $config),
                    'mail' => $this->prepareMailHandlerInstance($name, $config),
                    'rotate' => $this->prepareRotateHandlerInstance($name, $config),
                    'noop' => new NoopHandler(),
                    default => throw new FrameworkException('Unknown log handler "' . $handler . '"'),
                };
            }
        }

        return $logHandlers;
    }

    /**
     * @param string          $name
     * @param ConfigInterface $config
     *
     * @return FileHandler
     */
    protected function prepareFileHandlerInstance(string $name, ConfigInterface $config): FileHandler
    {
        $filename = $config->get('app.logging.channels.' . $name . '.handlers.file.filename');

        return new FileHandler($filename);
    }

    /**
     * @param string          $name
     * @param ConfigInterface $config
     *
     * @return MailHandler
     */
    protected function prepareMailHandlerInstance(string $name, ConfigInterface $config): MailHandler
    {
        $dsn = $config->get('app.logging.channels.' . $name . '.handlers.mail.dsn');
        $from = $config->get('app.logging.channels.' . $name . '.handlers.mail.from');
        $to = $config->get('app.logging.channels.' . $name . '.handlers.mail.to');
        $subject = $config->get('app.logging.channels.' . $name . '.handlers.mail.subject');

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);
        $email = (new Email())->from($from)->to($to)->subject($subject);

        return new MailHandler($mailer, $email, new HtmlFormatter());
    }

    /**
     * @param string          $name
     * @param ConfigInterface $config
     *
     * @return RotateHandler
     */
    protected function prepareRotateHandlerInstance(string $name, ConfigInterface $config): RotateHandler
    {
        $directory = $config->get('app.logging.channels.' . $name . '.handlers.rotate.directory');
        $interval = $config->get(
            'app.logging.channels.' . $name . '.handlers.rotate.interval',
            RotateHandler::INTERVAL_DAY
        );

        return new RotateHandler($directory, $interval);
    }
}
