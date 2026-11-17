<?php

namespace JeffersonGoncalves\LgpdTakedown\Enums;

enum ReportStatus: string
{
    case Open = 'open';
    case Removed = 'removed';
    case Rejected = 'rejected';
}
