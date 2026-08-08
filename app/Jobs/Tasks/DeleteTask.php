<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class DeleteTask
{
    public function __construct(public Task $task) {}

    /** @throws Throwable */
    public function handle(DatabaseManager $database): void
    {
        $database->transaction(
            callback: fn() => $this->task->delete(),
            attempts: 3,
        );
    }
}
