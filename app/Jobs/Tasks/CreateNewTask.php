<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;;

final readonly class CreateNewTask
{
    public function __construct(public NewTask $payload) {}

    public function handle(): Task
    {
        return Task::query()->create($this->payload->toArray());
    }
}
