<?php

declare(strict_types=1);

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasMongoUlidKey;

final class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, HasMongoUlidKey;

    /**
     * @var list<string>
     */
    protected $fillable = [
        // 'project_id',
        'assigned_to',
        'name',
        'description',
        'status',
        'due_date',
        'tag_ids',
    ];

    protected $connection = 'mongodb';
    protected $table = 'tasks';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
