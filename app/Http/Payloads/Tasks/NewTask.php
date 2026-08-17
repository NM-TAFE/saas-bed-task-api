<?php

declare(strict_types=1);

namespace App\Http\Payloads\Tasks;

final readonly class NewTask
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $status,
        public ?string $dueDate,
        public ?string $assignedTo,
    ) {}


    /** @return array{name:string,description:?string,status:string,due_date:?string,assigned_to:?string} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->dueDate,
            'assigned_to' => $this->assignedTo,
        ];
    }
}
