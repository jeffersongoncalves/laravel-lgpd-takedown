<?php

namespace JeffersonGoncalves\LgpdTakedown;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LgpdTakedownServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-lgpd-takedown')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
