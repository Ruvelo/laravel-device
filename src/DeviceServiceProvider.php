<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Ruvelo\Device\Http\Middleware\RequestClientHints;
use Ruvelo\Device\Http\Middleware\ShareDevice;
use Ruvelo\Device\View\IpadScript;

final class DeviceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/device.php', 'device');

        $this->app->singleton(Detector::class, function (Application $app): Detector {
            $config = $app->make('config');
            $store = $config->get('device.cache.store');

            return new Detector(
                cache: is_string($store) && $store !== '' ? $app->make('cache')->store($store) : null,
                ttl: (int) $config->get('device.cache.ttl', 604800),
                maxLength: (int) $config->get('device.max_length', 1000),
                useClientHints: (bool) $config->get('device.client_hints.read', true),
                ipadCookie: self::ipadCookie($config->get('device.ipad_cookie')),
            );
        });

        // Scoped, not a singleton: one Agent per request, also under Octane.
        $this->app->scoped(Agent::class, fn (Application $app): Agent => Agent::fromRequest($app->make('request')));

        if (! $this->app->bound('agent')) {
            $this->app->alias(Agent::class, 'agent');
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/device.php' => $this->app->configPath('device.php'),
        ], 'device-config');

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('device.share', ShareDevice::class);
        $router->aliasMiddleware('device.hints', RequestClientHints::class);

        $config = $this->app->make('config');

        if ($config->get('device.request_macro', true)) {
            Request::macro('device', function (): Device {
                /** @var Request $this */
                return app(Detector::class)->fromRequest($this);
            });
        }

        if ($cookie = self::ipadCookie($config->get('device.ipad_cookie'))) {
            // Set by a script, so it can't be encrypted.
            EncryptCookies::except($cookie);
        }

        if ($config->get('device.blade_directives', true)) {
            $this->registerBladeDirectives();
        }
    }

    private function registerBladeDirectives(): void
    {
        $current = fn (): Device => $this->app->make(Detector::class)->fromRequest($this->app->make('request'));

        Blade::if('mobile', fn (): bool => $current()->isMobile());
        Blade::if('phone', fn (): bool => $current()->isPhone());
        Blade::if('tablet', fn (): bool => $current()->isTablet());
        Blade::if('desktop', fn (): bool => $current()->isDesktop());
        Blade::if('bot', fn (): bool => $current()->isBot);
        Blade::if('device', fn (DeviceType|string ...$types): bool => $current()->is(...$types));

        Blade::directive('detectIpad', fn (): string => '<?php echo '.IpadScript::class.'::render(config(\'device.ipad_cookie\')); ?>');
    }

    private static function ipadCookie(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
