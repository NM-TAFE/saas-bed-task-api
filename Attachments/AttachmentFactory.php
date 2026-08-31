<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\PolymorphicRelations;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Attachment> */
final class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'attachmentable_id' => Task::factory(),
            'attachmentable_type' => PolymorphicRelations::ATTACHMENTABLE_TASK,
            'uploaded_by' => User::factory(),
            'disk' => 's3',
            'path' => 'attachments/' . $this->faker->uuid() . '.pdf',
            'original_name' => $this->faker->word() . '.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(1024, 5242880),
        ];
    }

    public function forProject(Project $project): self
    {
        return $this->state(fn() => [
            'attachmentable_id' => $project->id,
            'attachmentable_type' => PolymorphicRelations::ATTACHMENTABLE_PROJECT,
        ]);
    }

    public function forTask(Task $task): self
    {
        return $this->state(fn() => [
            'attachmentable_id' => $task->id,
            'attachmentable_type' => PolymorphicRelations::ATTACHMENTABLE_TASK,
        ]);
    }

    public function forMilestone(Milestone $milestone): self
    {
        return $this->state(fn() => [
            'attachmentable_id' => $milestone->id,
            'attachmentable_type' => PolymorphicRelations::ATTACHMENTABLE_MILESTONE,
        ]);
    }

    public function forComment(Comment $comment): self
    {
        return $this->state(fn() => [
            'attachmentable_id' => $comment->id,
            'attachmentable_type' => PolymorphicRelations::ATTACHMENTABLE_COMMENT,
        ]);
    }

    public function uploadedBy(User $user): self
    {
        return $this->state(fn() => [
            'uploaded_by' => $user->id,
        ]);
    }
}
