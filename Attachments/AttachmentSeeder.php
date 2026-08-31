<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Factories\AttachmentFactory;
use Illuminate\Database\Seeder;

final class AttachmentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            return;
        }

        Project::query()->each(fn(Project $project) => $this->seedProjectAttachment($project, $users->random()));
        Task::query()->each(fn(Task $task) => $this->seedTaskAttachment($task, $users->random()));
        Comment::query()->each(fn(Comment $comment) => $this->seedCommentAttachment($comment, $users->random()));
    }

    private function seedProjectAttachment(Project $project, User $user): void
    {
        AttachmentFactory::new()
            ->forProject($project)
            ->uploadedBy($user)
            ->create();
    }

    private function seedTaskAttachment(Task $task, User $user): void
    {
        AttachmentFactory::new()
            ->forTask($task)
            ->uploadedBy($user)
            ->create();
    }

    private function seedCommentAttachment(Comment $comment, User $user): void
    {
        AttachmentFactory::new()
            ->forComment($comment)
            ->uploadedBy($user)
            ->create();
    }
}
