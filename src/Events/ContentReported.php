<?php

namespace JeffersonGoncalves\LgpdTakedown\Events;

use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;

/** A new notice arrived. Route it to your moderation queue. */
class ContentReported
{
    public function __construct(public ContentReport $report) {}
}
