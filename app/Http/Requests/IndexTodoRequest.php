<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'status' => ['sometimes', 'string', Rule::in(['pending', 'open', 'in_progress', 'completed'])],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
