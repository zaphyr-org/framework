<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Contracts\LogManagerInterface;
use Zaphyr\Logger\Formatters\HtmlFormatter;
use Zaphyr\Logger\Formatters\LineFormatter;
use Zaphyr\Logger\Handlers\FileHandler;
use Zaphyr\Logger\Handlers\MailHandler;
use Zaphyr\Logger\Handlers\NoopHandler;
use Zaphyr\Logger\Handlers\RotateHandler;
use Zaphyr\Logger\Level;
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
        $this->getContainer()->bindSingleton(LogManagerInterface::class, function () {
            $logHandlers = $this->getLogHandlersFromConfig();

            return new LogManager($this->config('logging.default_channel', ''), $logHandlers);
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
     * @throws FrameworkException if the log handler is invalid
     * @return array<string, HandlerInterface[]>
     */
    protected function getLogHandlersFromConfig(): array
    {
        $logHandlers = [];

        foreach ($this->config('logging.channels', []) as $handlerName => $channel) {
            foreach ($channel['handlers'] as $handler => $handlerConfig) {
                $logHandlers[$handlerName][] = match ($handler) {
                    'file' => $this->prepareFileHandlerInstance($handlerName),
                    'mail' => $this->prepareMailHandlerInstance($handlerName),
                    'rotate' => $this->prepareRotateHandlerInstance($handlerName),
                    'noop' => new NoopHandler(),
                    default => throw new FrameworkException('Unknown log handler "' . $handler . '"'),
                };
            }
        }

        return $logHandlers;
    }

    /**
     * @param string $handlerName
     *
     * @return FileHandler
     */
    protected function prepareFileHandlerInstance(string $handlerName): FileHandler
    {
        $filename = $this->config('logging.channels.' . $handlerName . '.handlers.file.filename');
        $level = $this->config('logging.channels.' . $handlerName . '.handlers.file.level', 'debug');

        /** @var FormatterInterface $formatter */
        $formatter = $this->config(
            'logging.channels.' . $handlerName . '.handlers.file.formatter',
            LineFormatter::class
        );

        return new FileHandler($filename, new $formatter(), Level::fromName($level));
    }

    /**
     * @param string $handlerName
     *
     * @return MailHandler
     */
    protected function prepareMailHandlerInstance(string $handlerName): MailHandler
    {
        $dsn = $this->config('logging.channels.' . $handlerName . '.handlers.mail.dsn');
        $from = $this->config('logging.channels.' . $handlerName . '.handlers.mail.from');
        $to = $this->config('logging.channels.' . $handlerName . '.handlers.mail.to');
        $subject = $this->config('logging.channels.' . $handlerName . '.handlers.mail.subject');
        $level = $this->config('logging.channels.' . $handlerName . '.handlers.mail.level', 'debug');

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);
        $email = (new Email())->from($from)->to($to)->subject($subject);

        /** @var FormatterInterface $formatter */
        $formatter = $this->config(
            'logging.channels.' . $handlerName . '.handlers.mail.formatter',
            HtmlFormatter::class
        );

        return new MailHandler($mailer, $email, new $formatter(), Level::fromName($level));
    }

    /**
     * @param string $handlerName
     *
     * @return RotateHandler
     */
    protected function prepareRotateHandlerInstance(string $handlerName): RotateHandler
    {
        $directory = $this->config('logging.channels.' . $handlerName . '.handlers.rotate.directory');
        $interval = $this->config(
            'logging.channels.' . $handlerName . '.handlers.rotate.interval',
            RotateHandler::INTERVAL_DAY
        );
        $level = $this->config('logging.channels.' . $handlerName . '.handlers.rotate.level', 'debug');

        /** @var FormatterInterface $formatter */
        $formatter = $this->config(
            'logging.channels.' . $handlerName . '.handlers.rotate.formatter',
            LineFormatter::class
        );

        return new RotateHandler($directory, $interval, new $formatter(), Level::fromName($level));
    }
}
