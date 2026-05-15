<?php

declare(strict_types=1);

namespace Lightit\Shared\App\Providers;

use Carbon\CarbonImmutable;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::prohibitDestructiveCommands($this->app->environment('production'));

        if ($this->app->environment('production') && Config::boolean('app.debug')) {
            throw new \RuntimeException('Debug mode is enabled in production environment');
        }

        Model::shouldBeStrict(! $this->app->environment('production'));

        RateLimiter::for('api', function (Request $request) {
            $rateLimiter = Config::integer('app.rate.limit');

            return Limit::perMinute($rateLimiter)
                ->by($request->user()?->id ?: $request->ip());
        });

        Date::use(CarbonImmutable::class);

        Password::defaults(
            fn () => Password::min(8)
                ->max(68)
                ->mixedCase()
                ->numbers()
                ->symbols()
        );

        Email::defaults(
            fn () => Rule::email()
                ->rfcCompliant(strict: true)
                ->when(
                    $this->app->environment('production'),
                    fn (Email $rule) => $rule
                        ->validateMxRecord()
                        ->preventSpoofing()
                        ->withNativeValidation()
                )
        );

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                /** @var SecurityScheme $securityScheme */
                $securityScheme = SecurityScheme::http('bearer');
                $openApi->secure($securityScheme);
            })
            ->routes(fn (Route $route) => Str::startsWith($route->uri, 'api/'));

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
