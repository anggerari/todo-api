<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChartDataRequest;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChartController extends Controller
{
    /**
     * Main entry point for getting chart data.
     */
    public function getData(ChartDataRequest $request): JsonResponse
    {
        $type = $request->validated('type');
        $methodName = 'get' . Str::studly($type) . 'Summary';
        $data = $this->$methodName();
        return response()->json($data);
    }

    /**
     * Generates the summary data for statuses, ensuring all statuses are present.
     */
    private function getStatusSummary(): array
    {
        $allStatuses = ['pending', 'open', 'in_progress', 'completed'];

        $summaryTemplate = array_fill_keys($allStatuses, 0);

        $dbCounts = Todo::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $finalSummary = collect($summaryTemplate)->merge($dbCounts);

        return ['status_summary' => $finalSummary];
    }

    /**
     * Generates the summary data for priorities, ensuring all priorities are present.
     */
    private function getPrioritySummary(): array
    {
        $allPriorities = ['low', 'medium', 'high'];

        $summaryTemplate = array_fill_keys($allPriorities, 0);

        $dbCounts = Todo::query()
            ->select('priority', DB::raw('count(*) as count'))
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $finalSummary = collect($summaryTemplate)->merge($dbCounts);

        return ['priority_summary' => $finalSummary];
    }

    /**
     * Generates the complex summary data for assignees.
     */
    private function getAssigneeSummary(): array
    {
        $summary = Todo::query()
            ->select(
                'assignee',
                DB::raw('count(*) as total_todos'),
                DB::raw("count(case when status = 'pending' then 1 end) as total_pending_todos"),
                DB::raw("sum(case when status = 'completed' then time_tracked else 0 end) as total_timetracked_completed_todos")
            )
            ->whereNotNull('assignee')
            ->where('assignee', '!=', '')
            ->groupBy('assignee')
            ->get();

        $formattedSummary = $summary->mapWithKeys(function ($item) {
            return [$item->assignee => [
                'total_todos' => $item->total_todos,
                'total_pending_todos' => $item->total_pending_todos,
                'total_timetracked_completed_todos' => (int) $item->total_timetracked_completed_todos,
            ]];
        });

        return ['assignee_summary' => $formattedSummary];
    }
}
