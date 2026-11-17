<?php

namespace JeffersonGoncalves\LgpdTakedown\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Implement on the models users can report (posts, comments, videos...).
 */
interface ReportableContent
{
    /** Hide the content from everyone but keep it: it may be evidence (art. 27, §§2 and 3). */
    public function takeDown(): void;

    /** Put the content back after a successful appeal (art. 30). */
    public function restore(): void;

    /** Who published it, to be told about the removal and the appeal (art. 30). */
    public function contentAuthor(): ?Model;
}
