<?php

namespace JeffersonGoncalves\LgpdTakedown\Enums;

enum ReporterRole: string
{
    case Victim = 'victim';

    /** Parent, guardian or legal representative of the victim. */
    case Representative = 'representative';

    case PublicProsecutor = 'public_prosecutor';

    /** Entity that defends the rights of children and adolescents. */
    case Entity = 'entity';

    case User = 'user';

    /** Notices that oblige removal without a court order (Lei 15.211/2025, art. 29). */
    public function bypassesCourtOrder(): bool
    {
        return $this !== self::User;
    }
}
