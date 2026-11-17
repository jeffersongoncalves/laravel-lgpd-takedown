<?php

namespace JeffersonGoncalves\LgpdTakedown\Events;

use Illuminate\Database\Eloquent\Model;
use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;

/**
 * Tell the author: the reason ($report->decision), whether the analysis was automated
 * ($report->automated), and how to appeal until $report->appeal_until (art. 30). Then call
 * LgpdTakedown::authorNotified().
 */
class ContentRemoved
{
    public function __construct(public ContentReport $report, public ?Model $author) {}
}
