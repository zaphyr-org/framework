<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Exceptions\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\RunInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class WhoopsDebugHandler
{
    /**
     * @param RunInterface            $run
     * @param HandlerInterface        $handler
     * @param array<string, string[]> $blacklist
     */
    public function __construct(
        protected RunInterface $run = new Run(),
        protected HandlerInterface $handler = new PrettyPageHandler(),
        protected array $blacklist = []
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $throwable
     *
     * @return string
     */
    public function render(ServerRequestInterface $request, Throwable $throwable): string
    {
        if ($this->handler instanceof PrettyPageHandler) {
            /** @var PrettyPageHandler $handler */
            $handler = $this->handler;

            $this->prepareHandlerRequestDataTable($handler, $request);
            $this->prepareHandlerHeadersDataTable($handler, $request);
            $this->prepareHandlerBlacklist($handler);

            $handler->handleUnconditionally(true);
        }

        $this->run->pushHandler($this->handler);
        $this->run->writeToOutput(false);
        $this->run->allowQuit(false);

        return $this->run->handleException($throwable);
    }

    /**
     * @param PrettyPageHandler      $handler
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function prepareHandlerRequestDataTable(PrettyPageHandler $handler, ServerRequestInterface $request): void
    {
        $handler->addDataTable('Request', [
            'URI' => (string)$request->getUri(),
            'Protocol version' => $request->getProtocolVersion(),
            'Method' => $request->getMethod(),
            'Path' => $request->getUri()->getPath(),
            'Host' => $request->getUri()->getHost(),
            'Port' => $request->getUri()->getPort(),
            'Scheme' => $request->getUri()->getScheme(),
        ]);
    }

    /**
     * @param PrettyPageHandler      $handler
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function prepareHandlerHeadersDataTable(PrettyPageHandler $handler, ServerRequestInterface $request): void
    {
        $headers = array_map(static function ($value) {
            return implode(', ', $value);
        }, $request->getHeaders());

        $handler->addDataTable('Headers', $headers);
    }

    /**
     * @param PrettyPageHandler $handler
     *
     * @return void
     */
    protected function prepareHandlerBlacklist(PrettyPageHandler $handler): void
    {
        foreach ($this->blacklist as $superGlobalName => $values) {
            foreach ($values as $value) {
                $handler->blacklist($superGlobalName, $value);
            }
        }
    }
}
