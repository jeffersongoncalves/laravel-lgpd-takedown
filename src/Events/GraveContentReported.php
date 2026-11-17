<?php

namespace JeffersonGoncalves\LgpdTakedown\Events;

use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;

/**
 * Apparent exploitation, sexual abuse, kidnapping or grooming. Report it to the Polícia Federal
 * (Centro Nacional de Triagem de Notificações, Decreto 12.880/2026, arts. 36 and 37), then call
 * LgpdTakedown::reportedToAuthority().
 */
class GraveContentReported
{
    public function __construct(public ContentReport $report) {}
}
