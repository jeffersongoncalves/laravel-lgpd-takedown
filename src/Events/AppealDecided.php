<?php

namespace JeffersonGoncalves\LgpdTakedown\Events;

use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;

/** Tell the author the outcome ($report->restored_at set = content back online). */
class AppealDecided
{
    public function __construct(public ContentReport $report) {}
}
