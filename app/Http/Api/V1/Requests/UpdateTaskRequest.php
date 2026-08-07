<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTaskRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'project_id' => ['sometimes', 'required', 'string', 'exists:projects,id'],
            'assigned_to' => ['sometimes', 'nullable', 'string', 'exists:users,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::in(['todo', 'in_progress', 'done'])],
            'due_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }
}
