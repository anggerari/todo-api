<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTodoRequest extends FormRequest
{
    /**
     * Determines if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'assignee' => 'nullable|string|max:255',
            'time_tracked' => 'sometimes|numeric|min:0',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'required|date|after_or_equal:today',
            'status' => 'sometimes|in:pending,open,in_progress,completed',
        ];
    }

    /**
     * Get the custom validation messages for the defined rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your todo item.',
            'priority.in' => 'Priority must be one of the following: low, medium, or high.',
            'due_date.required' => 'A due date is required.',
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
        ];
    }
}
