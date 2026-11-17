<?php

namespace JeffersonGoncalves\LgpdTakedown;

use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\LgpdTakedown\Commands\PruneCommand;
use JeffersonGoncalves\LgpdTakedown\Commands\TakedownCommand;
use JeffersonGoncalves\LgpdTakedown\Http\Controllers\StoreReportController;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LgpdTakedownServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lgpd-takedown')
            ->hasConfigFile()
            ->hasMigrations(['create_lgpd_content_reports_table'])
            ->hasCommands([TakedownCommand::class, PruneCommand::class]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TakedownManager::class);
    }

    public function packageBooted(): void
    {
        if (! config('lgpd-takedown.route.enabled', false)) {
            return;
        }

        Route::middleware((array) config('lgpd-takedown.route.middleware', ['web']))
            ->post((string) config('lgpd-takedown.route.prefix', 'lgpd/takedown'), StoreReportController::class)
            ->name((string) config('lgpd-takedown.route.name', 'lgpd-takedown.store'));
    }
}
