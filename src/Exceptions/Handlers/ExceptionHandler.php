<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Exceptions\Handlers;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Http\Exceptions\HttpExceptionInterface;
use Zaphyr\Framework\Http\Exceptions\HttpException;
use Zaphyr\Framework\Http\Response;
use Zaphyr\Framework\Http\Utils\StatusCode;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;
use Zaphyr\Router\Exceptions\MethodNotAllowedException;
use Zaphyr\Router\Exceptions\NotFoundException;
use Zaphyr\Utils\Template;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var string|null
     */
    protected static ?string $reservedMemory;

    /**
     * @param ApplicationInterface $application
     */
    public function __construct(protected ApplicationInterface $application)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        self::$reservedMemory = str_repeat('x', 10240);

        error_reporting(-1);

        // The phpstan-ignore-next-line should not be here.
        // But it is fucking freaky to escape ignore messages
        // in neon files
        // @phpstan-ignore-next-line
        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws ErrorException
     * @return void
     */
    public function handleError(int $level, string $message, string $file, int $line): void
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * @param Throwable $throwable
     *
     * @return void
     */
    public function handleException(Throwable $throwable, ?OutputInterface $output = null): void
    {
        self::$reservedMemory = null;

        if ($this->application->isRunningInConsole()) {
            (new Application())->renderThrowable($throwable, $output ?? new ConsoleOutput());
        } else {
            $container = $this->application->getContainer();
            $container->get(EmitterInterface::class)->emit(
                $this->render($container->get(ServerRequestInterface::class), $throwable)
            );
        }
    }

    /**
     * @param array<string, mixed>|null $error
     */
    public function handleShutdown(?array $error = null): void
    {
        $error ??= error_get_last();

        if ($error !== null && $this->isFatal($error['type'])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * @param int $type
     *
     * @return bool
     */
    protected function isFatal(int $type): bool
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE], true);
    }

    /**
     * {@inheritdoc}
     */
    public function report(Throwable $throwable): void
    {
        if ($this->ignore($throwable)) {
            return;
        }

        if (method_exists($throwable, 'report')) {
            $throwable->report();

            return;
        }

        $this->application->getContainer()->get(LoggerInterface::class)->error(
            $throwable->getMessage(),
            ['exception' => $throwable]
        );
    }

    /**
     * @param Throwable $throwable
     *
     * @return bool
     */
    protected function ignore(Throwable $throwable): bool
    {
        $ignore = $this->application->getContainer()->get(ConfigInterface::class)->get('app.logging.ignore', []);

        foreach ($ignore as $type) {
            if ($throwable instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ServerRequestInterface $request, Throwable $throwable): ResponseInterface
    {
        if (method_exists($throwable, 'render') && $response = $throwable->render($request)) {
            return $response;
        }

        $config = $this->application->getContainer()->get(ConfigInterface::class);
        $debug = $config->get('app.debug', false);

        if ($debug) {
            return $this->renderDebugException($request, $throwable, $config->get('app.debug_blacklist', []));
        }

        return $this->renderHttpException($request, $throwable);
    }

    /**
     * @param ServerRequestInterface  $request
     * @param Throwable               $throwable
     * @param array<string, string[]> $blacklist
     *
     * @return ResponseInterface
     */
    protected function renderDebugException(
        ServerRequestInterface $request,
        Throwable $throwable,
        array $blacklist = []
    ): ResponseInterface {
        $response = $this->getResponse($throwable);
        $response->getBody()->write((new WhoopsDebugHandler(blacklist: $blacklist))->render($request, $throwable));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $throwable
     *
     * @return ResponseInterface
     */
    protected function renderHttpException(ServerRequestInterface $request, Throwable $throwable): ResponseInterface
    {
        $response = $this->getResponse($throwable);

        if ($request->getHeaderLine('Content-Type') === 'application/json') {
            return $this->renderJsonView($response, $throwable);
        }

        return $this->renderHtmlView($response, $throwable);
    }

    /**
     * @param Throwable $throwable
     *
     * @return ResponseInterface
     */
    protected function renderJsonView(ResponseInterface $response, Throwable $throwable): ResponseInterface
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->buildJsonResponse($response);
        }

        return (new HttpException($this->getResponseStatusCode($throwable)))->buildJsonResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @param Throwable         $throwable
     *
     * @return ResponseInterface
     */
    protected function renderHtmlView(ResponseInterface $response, Throwable $throwable): ResponseInterface
    {
        $response->getBody()->write(
            Template::render(
                dirname(__DIR__, 3) . '/views/errors.html',
                [
                    'status' => (string)$response->getStatusCode(),
                    'message' => $response->getReasonPhrase(),
                ]
            )
        );

        return $response;
    }

    /**
     * @param Throwable $throwable
     *
     * @return ResponseInterface
     */
    protected function getResponse(Throwable $throwable): ResponseInterface
    {
        $headers = $throwable instanceof HttpExceptionInterface ? $throwable->getHeaders() : [];

        return new Response(statusCode: $this->getResponseStatusCode($throwable), headers: $headers);
    }

    /**
     * @param mixed $throwable
     *
     * @return int
     */
    protected function getResponseStatusCode(mixed $throwable): int
    {
        return match (true) {
            $throwable instanceof HttpExceptionInterface => $throwable->getStatusCode(),
            $throwable instanceof MethodNotAllowedException => StatusCode::METHOD_NOT_ALLOWED,
            $throwable instanceof NotFoundException => StatusCode::NOT_FOUND,
            default => StatusCode::INTERNAL_SERVER_ERROR,
        };
    }
}
