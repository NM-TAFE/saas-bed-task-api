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
        $data = $this->validated();

        return new NewTask(
            name: $data['name'] ?? $task->name,
            description: $data['description'] ?? $task->description,
            status: $data['status'] ?? $task->status,
            dueDate: $data['due_date'] ?? $task->due_date?->format('Y-m-d'),
            assignedTo: $data['assigned_to'] ?? $task->assigned_to,
        );
    }
}
