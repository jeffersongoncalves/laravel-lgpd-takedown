<?php

namespace JeffersonGoncalves\LgpdTakedown\Commands;

use Illuminate\Console\Command;
use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;

class PruneCommand extends Command
{
    public $signature = 'lgpd:takedown-prune';

    public $description = 'Delete notices whose retention period ended (Marco Civil, art. 15), unless an appeal is still open';

    public function handle(): int
    {
        $deleted = ContentReport::query()
            ->scopes('retentionExpired')
            ->where(fn ($q) => $q->whereNull('appealed_at')->orWhereNotNull('appeal_decided_at'))
            ->delete();

        $this->components->info("{$deleted} notice(s) deleted.");

        return self::SUCCESS;
    }
}
