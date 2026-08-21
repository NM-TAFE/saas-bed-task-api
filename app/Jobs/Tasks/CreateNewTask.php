<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;
use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;;

final readonly class CreateNewTask
{
    public function __construct(public NewTask $payload) {}

    public function handle(): Task
    {
        // dd($this->payload);

        return $database->transaction(
            callback: fn(): Task => Task::query()->create($this->payload->toArray()),
            attempts: 3,
        );
        return Task::query()->create($this->payload->toArray());
    }
}
