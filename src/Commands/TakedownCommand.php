<?php

namespace JeffersonGoncalves\LgpdTakedown\Commands;

use Illuminate\Console\Command;
use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;

class TakedownCommand extends Command
{
    public $signature = 'lgpd:takedown {--json : Print the report as JSON}';

    public $description = 'List open notices, grave removals not reported to the authorities and appeals waiting for a decision';

    public function handle(): int
    {
        $row = fn (ContentReport $r): array => [
            'id' => $r->id,
            'category' => $r->category->value,
            'content' => $r->content_type.'#'.$r->content_id,
            'reported_at' => $r->created_at->toIso8601String(),
        ];

        $sections = [
            'open' => ContentReport::query()->scopes('open')->oldest()->get()->map($row)->all(),
            'authority_report_pending' => ContentReport::query()->scopes('owingAuthorityReport')->oldest()->get()->map($row)->all(),
            'appeals' => ContentReport::query()->scopes('awaitingAppealDecision')->oldest('appealed_at')->get()->map($row)->all(),
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($sections, JSON_PRETTY_PRINT));
        } else {
            foreach ([
                'open' => 'open notice(s).',
                'authority_report_pending' => 'grave removal(s) not reported to the authorities (art. 27).',
                'appeals' => 'appeal(s) waiting for a decision (art. 30).',
            ] as $key => $label) {
                $this->components->info(count($sections[$key]).' '.$label);
                $this->table(['ID', 'Category', 'Content', 'Reported at'], $sections[$key]);
            }
        }

        return array_filter($sections) === [] ? self::SUCCESS : self::FAILURE;
    }
}
