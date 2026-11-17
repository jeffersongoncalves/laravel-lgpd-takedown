<?php

namespace JeffersonGoncalves\LgpdTakedown\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JeffersonGoncalves\LgpdTakedown\Contracts\ReportableContent;

class Post extends Model implements ReportableContent
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = ['hidden_at' => 'datetime'];

    public function takeDown(): void
    {
        $this->forceFill(['hidden_at' => now()])->save();
    }

    public function restore(): void
    {
        $this->forceFill(['hidden_at' => null])->save();
    }

    public function contentAuthor(): ?Model
    {
        return Person::find($this->person_id);
    }
}
