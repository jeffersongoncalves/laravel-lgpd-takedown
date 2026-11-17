<?php

namespace JeffersonGoncalves\LgpdTakedown\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\LgpdTakedown\LgpdTakedown
 */
class LgpdTakedown extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-lgpd-takedown';
    }
}
