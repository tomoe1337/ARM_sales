<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task)
    {
        return $user->isHead() || $user->id === $task->user_id || $user->id === $task->assignee_id
            ? Response::allow()
            : Response::deny('У вас нет доступа к этой задаче');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task)
    {
        return $user->isHead() || $user->id === $task->user_id || $user->id === $task->assignee_id
            ? Response::allow()
            : Response::deny('У вас нет прав на редактирование этой задачи');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task)
    {
        return $user->isHead() || $user->id === $task->user_id
            ? Response::allow()
            : Response::deny('У вас нет прав на удаление этой задачи');
    }
} 