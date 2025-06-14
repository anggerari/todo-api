<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TodoExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'assignee' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'min' => 'nullable|numeric|min:0',
            'max' => 'nullable|numeric|min:0|gte:min',
        ];
    }
}
