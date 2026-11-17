<?php

namespace JeffersonGoncalves\LgpdTakedown\Tests;

use JeffersonGoncalves\LgpdTakedown\LgpdTakedownServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LgpdTakedownServiceProvider::class,
        ];
    }
}
