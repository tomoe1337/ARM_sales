<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * Retrieve tasks based on user role.
     *
     * @param User $user
     * @return Collection
     */
    public function getTasks(User $user): Collection
    {
        if ($user->isHead()) {
            // Руководитель видит все задачи
            $tasks = Task::with(['assignee'])
                ->latest()
                ->get();
        } else {
            // Обычный пользователь видит только свои задачи
            $tasks = Task::where('assignee_id', $user->id)
                ->with(['assignee'])
                ->latest()
                ->get();
        }

        return $tasks;
    }

    /**
     * Get a list of users who can be assigned tasks.
     *
     * @param User $user
     * @return Collection
     */
    public function getAssignableEmployees(User $user): Collection
    {
        return $user->isHead()
            ? User::where('id', '!=', $user->id)->get()
            : collect();
    }

    /**
     * Create a new task.
     *
     * @param array $data
     * @param User $user
     * @return Task
     * @throws ValidationException
     */
    public function createTask(array $data, User $user): Task
    {
        $validated = $data;
        $validated['user_id'] = $user->id;

        // Если пользователь не руководитель, он может быть только исполнителем своей задачи
        if (!$user->isHead()) {
            $validated['assignee_id'] = $user->id;
        }

        return Task::create($validated);
    }

    /**
     * Update the given task.
     *
     * @param Task $task
     * @param array $data
     * @param User $user
     * @return Task
     * @throws ValidationException
     */
    public function updateTask(Task $task, array $data, User $user): Task
    {
        $validated = $data;

        $validated = $validator->validated();

        // Если пользователь не руководитель, он может быть только исполнителем своей задачи
        if (!$user->isHead()) {
            $validated['assignee_id'] = $user->id;
        }

        $task->update($validated);

        return $task;
    }

    /**
     * Delete the given task.
     *
     * @param Task $task
     * @return bool|null
     */
    public function deleteTask(Task $task): ?bool
    {
        return $task->delete();
    }
}