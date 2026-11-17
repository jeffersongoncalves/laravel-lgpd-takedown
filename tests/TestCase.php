<?php

namespace JeffersonGoncalves\LgpdTakedown\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\LgpdTakedown\LgpdTakedownServiceProvider;
use JeffersonGoncalves\LgpdTakedown\Tests\Fixtures\Post;
use JeffersonGoncalves\PestPluginDatabaseMigrations\LoadsPackageMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use LoadsPackageMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            LgpdTakedownServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('lgpd-takedown.route.enabled', true);
        $app['config']->set('lgpd-takedown.route.middleware', []);
        $app['config']->set('lgpd-takedown.reportables', ['post' => Post::class]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadPackageMigrations(LgpdTakedownServiceProvider::class);

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id');
            $table->string('body');
            $table->timestamp('hidden_at')->nullable();
        });
    }
}
