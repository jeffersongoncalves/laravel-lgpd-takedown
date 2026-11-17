<?php

namespace JeffersonGoncalves\LgpdTakedown\Enums;

enum ReportCategory: string
{
    case SexualExploitation = 'sexual_exploitation';
    case SexualAbuse = 'sexual_abuse';
    case Kidnapping = 'kidnapping';
    case Grooming = 'grooming';
    case Violence = 'violence';
    case SelfHarm = 'self_harm';
    case Bullying = 'bullying';
    case Gambling = 'gambling';
    case PredatoryAdvertising = 'predatory_advertising';
    case Other = 'other';

    /**
     * Apparent exploitation, sexual abuse, kidnapping or grooming: remove and report to the
     * authorities (Lei 15.211/2025, art. 27; Decreto 12.880/2026, arts. 36 and 37).
     */
    public function isGrave(): bool
    {
        return in_array($this, [self::SexualExploitation, self::SexualAbuse, self::Kidnapping, self::Grooming], true);
    }
}
