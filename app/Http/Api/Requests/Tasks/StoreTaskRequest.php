<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Api\Payloads\Tasks\NewTask;

final class StoreTaskRequest extends FormRequest
{
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
