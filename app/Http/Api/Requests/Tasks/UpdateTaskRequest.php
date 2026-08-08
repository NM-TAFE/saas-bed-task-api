<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Tasks;

use App\Http\Api\Payloads\NewTask;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTaskRequest extends FormRequest
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
            'assigned_to' => ['sometimes', 'nullable', 'string', 'exists:users,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::in(['todo', 'in_progress', 'done'])],
            'due_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    public function payload(Task $task): NewTask
    {
        return new NewTask(
            name: (string) ($this->validated('name') ?? $task->name),
            description: $this->validated('description') ?? $task->description,
            status: (string) ($this->validated('status') ?? $task->status),
            dueDate: $this->validated('due_date') ?? $task->due_date?->format('Y-m-d'),
            assignedTo: $this->validated('assigned_to') ?? $task->assigned_to,
        );
    }
}
