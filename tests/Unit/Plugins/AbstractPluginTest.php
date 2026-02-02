<?php

namespace Zaphyr\FrameworkTests\Unit\Plugins;

use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Plugins\AbstractPlugin;

class AbstractPluginTest extends TestCase
{
    protected AbstractPlugin $plugin;

    protected function setUp(): void
    {
        $this->plugin = new class extends AbstractPlugin {
        };
    }

    protected function tearDown(): void
    {
        unset($this->plugin);
    }

    /* -------------------------------------------------
     * PROVIDERS
     * -------------------------------------------------
     */

    public function testProviders(): void
    {
        self::assertEmpty($this->plugin::providers());
    }

    /* -------------------------------------------------
     * COMMANDS
     * -------------------------------------------------
     */

    public function testCommands(): void
    {
        self::assertEmpty($this->plugin::commands());
    }

    /* -------------------------------------------------
     * CONTROLLERS
     * -------------------------------------------------
     */

    public function testControllers(): void
    {
        self::assertEmpty($this->plugin::controllers());
    }

    /* -------------------------------------------------
     * MIDDLEWARE
     * -------------------------------------------------
     */

    public function testMiddleware(): void
    {
        self::assertEmpty($this->plugin::middleware());
    }

    /* -------------------------------------------------
     * EVENTS
     * -------------------------------------------------
     */

    public function testEvents(): void
    {
        self::assertEmpty($this->plugin::events());
    }
}
