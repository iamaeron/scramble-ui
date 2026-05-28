<?php

namespace Iamaeron\ScrambleUi;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScrambleUiServiceProvider extends PackageServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/dist' => public_path('vendor/scramble-ui'),
            ], 'scramble-ui-assets');
        }
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('scramble-ui')
            ->hasViews('scramble-ui');
    }

    public function bootingPackage(): void
    {
        $this->app->booted(function () {
            $this->overrideUiRoute();
        });
    }

    private function overrideUiRoute(): void
    {
        foreach (Scramble::getConfigurationsInstance()->all() as $api => $generatorConfig) {
            /** @var Router $router */
            $router = $this->app->get(Router::class);

            if ($generatorConfig->uiRoute) {
                $cb = is_callable($generatorConfig->uiRoute)
                    ? $generatorConfig->uiRoute
                    : fn($router, $action) => $router->get('/docs/api', $action);

                $cb($router, function (Generator $generator) use ($api) {
                    $config = Scramble::getGeneratorConfig($api);

                    return view('scramble-ui::docs', [
                        'spec' => $generator($config),
                        'config' => $config,
                    ]);
                })->middleware($generatorConfig->get('middleware', [RestrictedDocsAccess::class]));
            }
        }
    }
}