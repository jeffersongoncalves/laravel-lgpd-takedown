<?php

namespace JeffersonGoncalves\LgpdTakedown\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use JeffersonGoncalves\LgpdTakedown\Contracts\ReportableContent;
use JeffersonGoncalves\LgpdTakedown\Enums\ReportCategory;
use JeffersonGoncalves\LgpdTakedown\Enums\ReporterRole;
use JeffersonGoncalves\LgpdTakedown\TakedownManager;

/** Public notice channel (Lei 15.211/2025, arts. 28 and 29, §3). */
class StoreReportController
{
    public function __invoke(Request $request, TakedownManager $takedown): JsonResponse
    {
        /** @var array<string, class-string<Model>> $reportables */
        $reportables = (array) config('lgpd-takedown.reportables', []);

        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys($reportables))],
            'id' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::enum(ReporterRole::class)],
            'category' => ['required', Rule::enum(ReportCategory::class)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $content = $reportables[$data['type']]::query()->find($data['id']);

        abort_unless($content instanceof ReportableContent, 404);

        $user = $request->user();

        $report = $takedown->report(
            $content,
            $data['name'],
            $data['email'],
            ReporterRole::from($data['role']),
            ReportCategory::from($data['category']),
            $data['description'] ?? null,
            $user instanceof Model ? $user : null,
        );

        return response()->json(['id' => $report->id, 'status' => $report->status->value], 201);
    }
}
