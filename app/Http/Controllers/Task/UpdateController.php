<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Requests\UpdateTaskRequest; // Assuming you have an UpdateTaskRequest
use App\Services\TaskService; // Assuming you have a TaskService

class UpdateController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\UpdateTaskRequest  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();

        $user = auth()->user();

        // If the user is not a head, they can only be the assignee of their task
        if (!$user->isHead()) {
            $validated['assignee_id'] = $user->id;
        }

        $this->taskService->updateTask($task, $validated, $user);

        return redirect()->route('tasks.index')->with('success', 'Задача успешно обновлена');
    }
}