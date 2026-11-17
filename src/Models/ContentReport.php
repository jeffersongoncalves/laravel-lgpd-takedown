<?php

namespace JeffersonGoncalves\LgpdTakedown\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\LgpdTakedown\Enums\ReportCategory;
use JeffersonGoncalves\LgpdTakedown\Enums\ReporterRole;
use JeffersonGoncalves\LgpdTakedown\Enums\ReportStatus;

/**
 * A notice about content that violates the rights of children and adolescents (Lei 15.211/2025, arts. 27 to 30).
 *
 * @property int $id
 * @property string $content_type
 * @property int|string $content_id
 * @property string|null $reporter_type
 * @property int|string|null $reporter_id
 * @property string $reporter_name
 * @property string $reporter_email
 * @property ReporterRole $reporter_role
 * @property ReportCategory $category
 * @property string|null $description
 * @property bool $reporter_flagged
 * @property ReportStatus $status
 * @property string|null $decision
 * @property bool $automated
 * @property Carbon|null $decided_at
 * @property Carbon|null $authority_reported_at
 * @property string|null $authority_protocol
 * @property Carbon|null $retain_until
 * @property Carbon|null $author_notified_at
 * @property Carbon|null $appeal_until
 * @property string|null $appeal
 * @property Carbon|null $appealed_at
 * @property string|null $appeal_decision
 * @property Carbon|null $appeal_decided_at
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property-read Model|null $content
 * @property-read Model|null $reporter
 */
class ContentReport extends Model
{
    protected $guarded = [];

    protected $attributes = [
        'status' => 'open',
        'automated' => false,
        'reporter_flagged' => false,
    ];

    public function getTable(): string
    {
        return config('lgpd-takedown.table', 'lgpd_content_reports');
    }

    protected function casts(): array
    {
        return [
            'reporter_role' => ReporterRole::class,
            'category' => ReportCategory::class,
            'status' => ReportStatus::class,
            'reporter_flagged' => 'boolean',
            'automated' => 'boolean',
            'decided_at' => 'datetime',
            'authority_reported_at' => 'datetime',
            'retain_until' => 'datetime',
            'author_notified_at' => 'datetime',
            'appeal_until' => 'datetime',
            'appealed_at' => 'datetime',
            'appeal_decided_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function content(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reporter(): MorphTo
    {
        return $this->morphTo();
    }

    /** Grave content removed but not reported to the authorities yet (art. 27). */
    public function owesAuthorityReport(): bool
    {
        return $this->category->isGrave() && $this->status === ReportStatus::Removed && $this->authority_reported_at === null;
    }

    public function canAppeal(): bool
    {
        return $this->status === ReportStatus::Removed
            && $this->appealed_at === null
            && ($this->appeal_until === null || $this->appeal_until->isFuture());
    }

    public function awaitsAppealDecision(): bool
    {
        return $this->appealed_at !== null && $this->appeal_decided_at === null;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', ReportStatus::Open->value);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOwingAuthorityReport(Builder $query): void
    {
        $grave = array_map(fn (ReportCategory $c): string => $c->value, array_filter(ReportCategory::cases(), fn (ReportCategory $c): bool => $c->isGrave()));

        $query->where('status', ReportStatus::Removed->value)->whereIn('category', $grave)->whereNull('authority_reported_at');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeAwaitingAppealDecision(Builder $query): void
    {
        $query->whereNotNull('appealed_at')->whereNull('appeal_decided_at');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeRetentionExpired(Builder $query): void
    {
        $query->whereNotNull('retain_until')->where('retain_until', '<=', now());
    }
}
