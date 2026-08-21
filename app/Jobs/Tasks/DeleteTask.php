<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Models\Task;

final readonly class DeleteTask
{
    public function __construct(public Task $task) {}

    public function handle(): void
    {
        $this->task->delete();
    }
}
