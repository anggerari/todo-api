<?php

namespace App\Exports;

use App\Models\Todo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class TodosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected array $filters;
    protected int $totalTodos = 0;
    protected int $totalTimeTracked = 0;

    public function __construct(array $validatedFilters)
    {
        $this->filters = $validatedFilters;
    }

    public function headings(): array
    {
        return ['Title', 'Assignee', 'Due Date', 'Time Tracked', 'Status', 'Priority'];
    }

    public function map($todo): array
    {
        return [$todo->title, $todo->assignee, $todo->due_date, $todo->time_tracked, $todo->status, $todo->priority];
    }

    public function query()
    {
        $query = Todo::query();

        $query->when($this->filters['title'] ?? null, fn($q, $v) => $q->where('title', 'like', '%' . $v . '%'));
        $query->when($this->filters['start_date'] ?? null, fn($q, $v) => $q->where('due_date', '>=', $v));
        $query->when($this->filters['end_date'] ?? null, fn($q, $v) => $q->where('due_date', '<=', $v));
        $query->when($this->filters['min'] ?? null, fn($q, $v) => $q->where('time_tracked', '>=', $v));
        $query->when($this->filters['max'] ?? null, fn($q, $v) => $q->where('time_tracked', '<=', $v));

        foreach (['assignee', 'status', 'priority'] as $key) {
            $query->when($this->filters[$key] ?? null, function ($q, $value) use ($key) {
                return $q->whereIn($key, array_map('trim', explode(',', $value)));
            });
        }

        $summaryQuery = clone $query;
        $this->totalTodos = $summaryQuery->count();
        $this->totalTimeTracked = $summaryQuery->sum('time_tracked');

        return $query;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $summaryRow = $lastRow + 2;
                $timeRow = $lastRow + 3;

                $sheet->setCellValue("A{$summaryRow}", 'Total Todos:');
                $sheet->setCellValue("B{$summaryRow}", $this->totalTodos);

                $sheet->setCellValue("A{$timeRow}", 'Total Time Tracked:');
                $sheet->setCellValue("B{$timeRow}", $this->totalTimeTracked);

                $sheet->getStyle("A{$summaryRow}:A{$timeRow}")->getFont()->setBold(true);
            },
        ];
    }
}
