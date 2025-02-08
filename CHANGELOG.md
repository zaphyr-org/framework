# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 🔥 [v1.0.0-alpha.4](https://github.com/zaphyr-org/framework/compare/1.0.0-alpha.3...1.0.0-alpha.4) [2025-02-08]

### New:
* Added bin path to config path resolver
* Added `handleRequest` and `handleCommand` methods to Application class

### Changed:
* Changed `Application::getVersion` method from static to non-static

## 🔥 [v1.0.0-alpha.3](https://github.com/zaphyr-org/framework/compare/1.0.0-alpha.2...1.0.0-alpha.3) [2024-12-22]

### New:
* Added bin path to Application class

### Changed:
* Renamed "logs" commands to "log" and "routes" command to "router"
* Updated zaphyr-org/config to v2.3
* Changed app source code directory from "src" to "app"

### Fixed:
* Fixed path for router list command in phpstan.neon

## 🔥 [v1.0.0-alpha.2](https://github.com/zaphyr-org/framework/compare/1.0.0-alpha.1...1.0.0-alpha.2) [2024-05-11]

### Fixed:
* Fixed `Zaphyr\Framework\Providers\SessionServiceProvider` cookie domain and session name config
* Changed `getRootPath` to `getConfigPath` in `Zaphyr\Framework\Providers\Bootable\ConfigBootProvider::loadConfigItems`
* Bind default important interfaces to Application instance in `Zaphyr\Framework\Testing\AbstractTestCase` class

## 🔥 v1.0.0-alpha.1 [2024-05-06]

### New:
* Initial commit
* Added HTTP StatusCode class
* Added HTTP Response class
* Added HTTP EmptyResponse class
* Added HTTP RedirectResponse class
* Added HttpUtils class
* Added HTTP TextResponse class
* Added HTTP HtmlResponse class
* Added HTTP XmlResponse class
* Added HTTP JsonResponse class
* Added HTTP Request class
* Added HttpException class
* Added Application class
* Added ConfigBootProvider and config PathReplacer classes
* Added EnvironmentBootProvider class
* Added RouterBootProvider class
* Added RegisterServicesBootProvider class
* Added items to provide section in composer.json
* Added HttpKernel class
* Added TwigView class
* Added TwigRuntimeLoader class
* Added `isTestingEnvironment` method to Application class
* Added WhoopsDebugHandler class
* Added ExceptionHandler class
* Added IntegrationTestCase class
* Added ViewServiceProvider class
* Added EncryptServiceProvider class
* Changed visibility to protected for `setUp` and `tearDown methods in unit tests
* Added LogServiceProvider class
* Added SessionServiceProvider class
* Added CookieServiceProvider class
* Added 'hasSession' and 'getSession' methods to HTTP Request class
* Added CookieMiddleware class
* Added SessionMiddleware class
* Added twig view extensions
* Added XSSMiddleware class
* Added `isRunningInConsole` method to Application class
* Added session twig view extension
* Added CSRF twig view extension
* Added CSRFMiddleware class
* Added ConsoleKernel class
* Added app:environment command class
* Added config console commands
* Added views:clear console command class
* Added logs:clear console command class
* Added cache:clear console command class
* Added framework commands to ConsoleKernel class
* Added app:key console command class
* Added create console commands
* Added maintenance console commands
* Added events
* Added event and listener create console commands
* Added `.vscode` to .gitignore file
* Added HttpTestCase
* Added ConsoleTestCase
* Added initBindings, runHttpRequest and runConsoleCommand methods to Application class
* Added getAppPath and setAppPath methods to Application class
* Added getVersion method to Application class
* Added logger v2.1.0 with NoopHandler in LoggingServiceProvider
* Added list routes command
* Improved unit tests for console ClearCommand classes

### Changed:
* Improved HTTP response classes unit tests
* Improved HttUtils::normalizeFiles method
* Improved HTTP exceptions
* Used StatusCode constants in HTTP response classes
* Moved unit tests in `tests/Unit` directory
* Changed visibility to public for `bootstrap` method in HttpKernel class
* Splitted testsuites in Integration and Unit in phpunit.xml
* Refactored ExceptionHandler class and errors/fallback.html
* Improved XSSMiddleware class exception handling
* Updated zaphyr-org/config to v2.2
* Move providers config to services "namespace"
* Moved code in try block for HttpKernel class
* Refactored console clear commands
* Refactored config files
* Renamed "templates" directory to "views" directory
* Moved "Commands" namespace into "Console" namespace
* Improved error handling for create console commands
* Improved exception handling
* Improved unit tests
* Updated README.md
* Improved framework testing classes
* Improved configuration handling
* Major improvements on service providers
* Limitation to NEON config files reversed
* Added "abstract" keyword to AbstractClearCommand and AbstractCommand class
* Updated README.md

### Removed:
* Removed psr/http-message from require section in composer.json
* Removed phpstan/phpstan-phpunit from composer require-dev
* Removed view layer from framework
* Removed extension create command
* Removed ExtensionCommandTest
* Removed useless `JSON_ERROR_NONE !== json_last_error()` throw statement in JsonResponse class
* Removed initBindingsOverwrite, runHttpRequest method and runConsoleCommand method from Application class

### Fixed:
* Added missing FrameworkException class
* Fixed IntegrationTestCase container return type
* Moved `bootstrap` method outside of try block in ConsoleKernel class
* Fixed namespaces and strict_types in test classes
* Fixed dontReport config loading in ExceptionHandler class
* Moved `filp/whoops` to `require` section in composer.json
* Renamed property `$sessionHandler` to `$sessionHandlerMock` in SessionMiddlewareTest
* Fixed namespace in command.stub
* Improved tests for RouterBootProvider class
* RequestTrait::call method handles slash correctly
