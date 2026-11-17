<?php

namespace JeffersonGoncalves\LgpdTakedown\Facades;

use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\LgpdTakedown\TakedownManager;

/**
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport report(\Illuminate\Database\Eloquent\Model&\JeffersonGoncalves\LgpdTakedown\Contracts\ReportableContent $content, string $reporterName, string $reporterEmail, \JeffersonGoncalves\LgpdTakedown\Enums\ReporterRole $role, \JeffersonGoncalves\LgpdTakedown\Enums\ReportCategory $category, ?string $description = null, ?\Illuminate\Database\Eloquent\Model $reporter = null)
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport remove(\JeffersonGoncalves\LgpdTakedown\Models\ContentReport $report, string $decision, bool $automated = false)
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport reject(\JeffersonGoncalves\LgpdTakedown\Models\ContentReport $report, string $decision, bool $automated = false)
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport reportedToAuthority(\JeffersonGoncalves\LgpdTakedown\Models\ContentReport $report, ?string $protocol = null)
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport authorNotified(\JeffersonGoncalves\LgpdTakedown\Models\ContentReport $report)
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport appeal(\JeffersonGoncalves\LgpdTakedown\Models\ContentReport $report, string $text)
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport decideAppeal(\JeffersonGoncalves\LgpdTakedown\Models\ContentReport $report, bool $restore, string $decision)
 * @method static \JeffersonGoncalves\LgpdTakedown\Models\ContentReport extendRetention(\JeffersonGoncalves\LgpdTakedown\Models\ContentReport $report, \Carbon\CarbonInterface $until)
 * @method static bool isAbusiveReporter(string $email)
 *
 * @see TakedownManager
 */
class LgpdTakedown extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TakedownManager::class;
    }
}
