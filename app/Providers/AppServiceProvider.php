<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\SiteInfo;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteInfo::class, static function (Application $app): SiteInfo {
            /** @var Config $config */
            $config = $app->make(Config::class);
            /** @var array<string, mixed> $site */
            $site = $config->get('site', []);

            return SiteInfo::fromConfig($site);
        });
    }

    public function boot(): void
    {
        // Вне прода падать на ленивой загрузке, опечатках в атрибутах и
        // молча отброшенных полях: агент ошибается именно здесь.
        Model::shouldBeStrict(! $this->app->isProduction());

        Date::use(CarbonImmutable::class);

        // Форма заявки: не больше 3 в минуту и 20 в сутки с одного адреса.
        RateLimiter::for('leads', static function (Request $request): array {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(3)->by('leads-minute:'.$ip),
                Limit::perDay(20)->by('leads-day:'.$ip),
            ];
        });

        View::composer('*', function (\Illuminate\View\View $view): void {
            $view->with('site', $this->app->make(SiteInfo::class));
        });
    }
}
