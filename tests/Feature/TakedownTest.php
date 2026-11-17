<?php

use Illuminate\Support\Facades\Event;
use JeffersonGoncalves\LgpdTakedown\Enums\ReportCategory;
use JeffersonGoncalves\LgpdTakedown\Enums\ReporterRole;
use JeffersonGoncalves\LgpdTakedown\Enums\ReportStatus;
use JeffersonGoncalves\LgpdTakedown\Events\AppealDecided;
use JeffersonGoncalves\LgpdTakedown\Events\ContentRemoved;
use JeffersonGoncalves\LgpdTakedown\Events\ContentReported;
use JeffersonGoncalves\LgpdTakedown\Events\GraveContentReported;
use JeffersonGoncalves\LgpdTakedown\Facades\LgpdTakedown;
use JeffersonGoncalves\LgpdTakedown\Models\ContentReport;
use JeffersonGoncalves\LgpdTakedown\Tests\Fixtures\Person;
use JeffersonGoncalves\LgpdTakedown\Tests\Fixtures\Post;

beforeEach(function () {
    $this->author = Person::create(['name' => 'Author']);
    $this->post = Post::create(['person_id' => $this->author->id, 'body' => 'Hello']);
});

function notice(Post $post, ReportCategory $category = ReportCategory::Bullying, string $email = 'mae@example.com'): ContentReport
{
    return LgpdTakedown::report($post, 'Maria', $email, ReporterRole::Representative, $category, 'It targets my son.');
}

it('rejects anonymous notices', function () {
    LgpdTakedown::report($this->post, '', 'x@example.com', ReporterRole::User, ReportCategory::Other);
})->throws(InvalidArgumentException::class);

it('records a notice and leaves non-grave content online until decided', function () {
    Event::fake();

    $report = notice($this->post);

    expect($report->status)->toBe(ReportStatus::Open)
        ->and($report->reporter_role->bypassesCourtOrder())->toBeTrue()
        ->and($this->post->fresh()->hidden_at)->toBeNull();

    Event::assertDispatched(ContentReported::class);
    Event::assertNotDispatched(GraveContentReported::class);
});

it('takes grave content down at once and flags the authority report', function () {
    Event::fake([GraveContentReported::class, ContentRemoved::class]);

    $report = notice($this->post, ReportCategory::Grooming);

    expect($report->status)->toBe(ReportStatus::Removed)
        ->and($report->automated)->toBeTrue()
        ->and($report->retain_until->isAfter(now()->addMonths(5)))->toBeTrue()
        ->and($report->owesAuthorityReport())->toBeTrue()
        ->and($this->post->fresh()->hidden_at)->not->toBeNull();

    Event::assertDispatched(GraveContentReported::class);
    Event::assertDispatched(ContentRemoved::class, fn (ContentRemoved $e): bool => $e->author?->is($this->author) === true);

    LgpdTakedown::reportedToAuthority($report, 'PF-2026-1');

    expect($report->fresh()->owesAuthorityReport())->toBeFalse()
        ->and(ContentReport::query()->scopes('owingAuthorityReport')->count())->toBe(0);
});

it('keeps grave content for review when configured', function () {
    config()->set('lgpd-takedown.remove_grave_immediately', false);

    expect(notice($this->post, ReportCategory::SexualAbuse)->status)->toBe(ReportStatus::Open);
});

it('removes, takes the appeal and restores', function () {
    Event::fake([AppealDecided::class]);
    $report = LgpdTakedown::remove(notice($this->post), 'Bullying a classmate.');

    expect($report->canAppeal())->toBeTrue();

    LgpdTakedown::authorNotified($report);
    LgpdTakedown::appeal($report, 'It was a joke between friends, he agreed.');

    expect($report->awaitsAppealDecision())->toBeTrue()
        ->and(fn () => LgpdTakedown::appeal($report, 'again'))->toThrow(LogicException::class);

    LgpdTakedown::decideAppeal($report, true, 'Context confirmed by the other user.');

    expect($report->fresh()->restored_at)->not->toBeNull()
        ->and($this->post->fresh()->hidden_at)->toBeNull();
    Event::assertDispatched(AppealDecided::class);
});

it('closes the appeal window', function () {
    $report = LgpdTakedown::remove(notice($this->post), 'Bullying.');

    $this->travel(31)->days();

    expect($report->fresh()->canAppeal())->toBeFalse();
});

it('does not decide twice', function () {
    $report = LgpdTakedown::reject(notice($this->post), 'Not a violation.');

    LgpdTakedown::remove($report, 'Changed my mind');
})->throws(LogicException::class);

it('flags reporters with many rejected notices', function () {
    config()->set('lgpd-takedown.abuse.rejected', 2);

    LgpdTakedown::reject(notice($this->post, email: 'troll@example.com'), 'No');
    expect(notice($this->post, email: 'troll@example.com')->reporter_flagged)->toBeFalse();

    LgpdTakedown::reject(ContentReport::query()->scopes('open')->first(), 'No');
    expect(notice($this->post, email: 'TROLL@example.com')->reporter_flagged)->toBeTrue();
});

it('accepts notices through the public channel', function () {
    $this->postJson('/lgpd/takedown', [
        'type' => 'post', 'id' => $this->post->id,
        'name' => 'Maria', 'email' => 'mae@example.com',
        'role' => 'victim', 'category' => 'bullying',
    ])->assertCreated()->assertJson(['status' => 'open']);

    $this->postJson('/lgpd/takedown', ['type' => 'post', 'id' => $this->post->id, 'role' => 'user', 'category' => 'other'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);

    $this->postJson('/lgpd/takedown', [
        'type' => 'post', 'id' => 999, 'name' => 'A', 'email' => 'a@example.com', 'role' => 'user', 'category' => 'other',
    ])->assertNotFound();
});

it('reports pending work and prunes expired notices', function () {
    $report = LgpdTakedown::remove(notice($this->post), 'Bullying.');

    $this->artisan('lgpd:takedown')->assertSuccessful();

    LgpdTakedown::appeal($report, 'Please review');
    $this->artisan('lgpd:takedown')->assertFailed();

    $this->travel(7)->months();
    $this->artisan('lgpd:takedown-prune')->expectsOutputToContain('0 notice(s) deleted.');

    LgpdTakedown::decideAppeal($report, false, 'Kept.');
    $this->artisan('lgpd:takedown-prune')->expectsOutputToContain('1 notice(s) deleted.');
});
