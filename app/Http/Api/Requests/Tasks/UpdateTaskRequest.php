<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Tasks;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;

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
            'due_date' => ['sometimes', 'nullable', 'date_format:d-m-y'],
        ];
    }

    public function payload(Task $task): NewTask
    {
        $data = $this->validated();

        return new NewTask(
            name: (string) $data['name'] ?? $task->name,
            description: (string) $data['description'] ?? $task->description,
            status: (string) $data['status'] ?? $task->status,
            dueDate: $data['due_date'] ?? $task->due_date?->format('d-m-Y'),
            assignedTo: $data['assigned_to'] ?? $task->assigned_to,
        );
    }
}
