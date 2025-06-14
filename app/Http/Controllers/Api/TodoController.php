<?php

namespace App\Http\Controllers\Api;

use App\Exports\TodosExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\IndexTodoRequest;
use App\Http\Requests\TodoExportRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;

class TodoController extends Controller
{
    public function store(StoreTodoRequest $request): JsonResponse
    {
        $todo = Todo::create($request->validated());
        return (new TodoResource($todo))
            ->response()
            ->setStatusCode(201);
    }

    public function index(IndexTodoRequest $request): AnonymousResourceCollection
    {
        $query = Todo::query();

        $query->when($request->validated('status'), function ($q, $status) {
            return $q->where('status', $status);
        });

        $query->when($request->validated('priority'), function ($q, $priority) {
            return $q->where('priority', $priority);
        });

        $todos = $query->paginate(15);

        return TodoResource::collection($todos);
    }

    public function export(TodoExportRequest $request): BinaryFileResponse
    {
        $filename = 'todos_report_' . now()->format('Y-m-d_H-i') . '.xlsx';
        return Excel::download(new TodosExport($request->validated()), $filename);
    }
}
