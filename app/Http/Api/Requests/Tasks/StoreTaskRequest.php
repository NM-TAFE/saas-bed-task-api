<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Tasks;

use App\Http\Api\Payloads\NewTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assigned_to' => ['nullable', 'string', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function payload(): NewTask
    {
        return new NewTask(
            name: (string) $this->validated('name'),
            description: $this->validated('description'),
            status: (string) $this->validated('status'),
            dueDate: $this->validated('due_date'),
            assignedTo: $this->validated('assigned_to'),
        );
    }
}
