<?php

namespace JeffersonGoncalves\LgpdTakedown;

use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JeffersonGoncalves\LgpdTakedown\Contracts\ReportableContent;
use JeffersonGoncalves\LgpdTakedown\Enums\ReportCategory;
use JeffersonGoncalves\LgpdTakedown\Enums\ReporterRole;
use JeffersonGoncalves\LgpdTakedown\Enums\ReportStatus;
use JeffersonGoncalves\LgpdTakedown\Events\AppealDecided;
use JeffersonGoncalves\LgpdTakedown\Events\ContentRemoved;
use JeffersonGoncalves\LgpdTakedown\Events\ContentReported;
use JeffersonGoncalves\LgpdTakedown\Events\GraveContentReported;
use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;
use LogicException;

class TakedownManager
{
    /**
     * Record a notice. Anonymous notices are not allowed (art. 29, §2): the reporter must give a
     * name and an email. Grave categories are taken down right away (art. 27).
     */
    public function report(
        Model&ReportableContent $content,
        string $reporterName,
        string $reporterEmail,
        ReporterRole $role,
        ReportCategory $category,
        ?string $description = null,
        ?Model $reporter = null,
    ): ContentReport {
        if (trim($reporterName) === '' || filter_var($reporterEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Notices must identify the reporter (Lei 15.211/2025, art. 29, §2).');
        }

        $report = ContentReport::create([
            'content_type' => $content->getMorphClass(),
            'content_id' => $content->getKey(),
            'reporter_type' => $reporter?->getMorphClass(),
            'reporter_id' => $reporter?->getKey(),
            'reporter_name' => trim($reporterName),
            'reporter_email' => strtolower(trim($reporterEmail)),
            'reporter_role' => $role,
            'category' => $category,
            'description' => $description,
            'reporter_flagged' => $this->isAbusiveReporter($reporterEmail),
        ]);

        event(new ContentReported($report));

        if ($category->isGrave()) {
            event(new GraveContentReported($report));

            if (config('lgpd-takedown.remove_grave_immediately', true)) {
                $this->remove($report, 'Removed when reported: apparent violation under Lei 15.211/2025, art. 27.', automated: true);
            }
        }

        return $report;
    }

    /**
     * Take the content down. The data is kept for the Marco Civil period (art. 27, §§2 and 3)
     * and the author can appeal (art. 30).
     */
    public function remove(ContentReport $report, string $decision, bool $automated = false): ContentReport
    {
        $this->ensureOpen($report);

        $content = $this->content($report);
        $content?->takeDown();

        $report->forceFill([
            'status' => ReportStatus::Removed,
            'decision' => $decision,
            'automated' => $automated,
            'decided_at' => now(),
            'retain_until' => now()->add(CarbonInterval::make((string) config('lgpd-takedown.retention', '6 months'))),
            'appeal_until' => now()->add(CarbonInterval::make((string) config('lgpd-takedown.appeal_window', '30 days'))),
        ])->save();

        event(new ContentRemoved($report, $content?->contentAuthor()));

        return $report;
    }

    public function reject(ContentReport $report, string $decision, bool $automated = false): ContentReport
    {
        $this->ensureOpen($report);

        $report->forceFill([
            'status' => ReportStatus::Rejected,
            'decision' => $decision,
            'automated' => $automated,
            'decided_at' => now(),
        ])->save();

        return $report;
    }

    public function reportedToAuthority(ContentReport $report, ?string $protocol = null): ContentReport
    {
        $report->forceFill(['authority_reported_at' => now(), 'authority_protocol' => $protocol])->save();

        return $report;
    }

    public function authorNotified(ContentReport $report): ContentReport
    {
        $report->forceFill(['author_notified_at' => now()])->save();

        return $report;
    }

    public function appeal(ContentReport $report, string $text): ContentReport
    {
        if (! $report->canAppeal()) {
            throw new LogicException('This removal cannot be appealed: it was not removed, was already appealed or the window closed.');
        }

        $report->forceFill(['appeal' => $text, 'appealed_at' => now()])->save();

        return $report;
    }

    /** Decide the author's appeal. $restore puts the content back online. */
    public function decideAppeal(ContentReport $report, bool $restore, string $decision): ContentReport
    {
        if (! $report->awaitsAppealDecision()) {
            throw new LogicException('There is no appeal waiting for a decision.');
        }

        if ($restore) {
            $this->content($report)?->restore();
        }

        $report->forceFill([
            'appeal_decision' => $decision,
            'appeal_decided_at' => now(),
            'restored_at' => $restore ? now() : null,
        ])->save();

        event(new AppealDecided($report));

        return $report;
    }

    /** Keep the data longer, e.g. at the request of the authorities (art. 27, §3). */
    public function extendRetention(ContentReport $report, CarbonInterface $until): ContentReport
    {
        $report->forceFill(['retain_until' => $until])->save();

        return $report;
    }

    /** Too many rejected notices from this email in the configured window (arts. 32 and 33). */
    public function isAbusiveReporter(string $email): bool
    {
        $rejected = ContentReport::query()
            ->where('reporter_email', strtolower(trim($email)))
            ->where('status', ReportStatus::Rejected->value)
            ->where('decided_at', '>=', now()->sub(CarbonInterval::make((string) config('lgpd-takedown.abuse.within', '30 days'))))
            ->count();

        return $rejected >= (int) config('lgpd-takedown.abuse.rejected', 5);
    }

    /**
     * @return (Model&ReportableContent)|null
     */
    public function content(ContentReport $report): ?Model
    {
        $content = $report->content;

        return $content instanceof ReportableContent ? $content : null;
    }

    protected function ensureOpen(ContentReport $report): void
    {
        if ($report->status !== ReportStatus::Open) {
            throw new LogicException('This notice was already decided.');
        }
    }
}
