<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest; // Assuming you have a StoreTaskRequest
use App\Services\TaskService; // Assuming you have a TaskService

class StoreController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\StoreTaskRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(StoreTaskRequest $request)
    {
        $validatedData = $request->validated();
        $user = $request->user();

        // Delegate task creation to the TaskService
        $this->taskService->createTask($validatedData, $user);

        return redirect()->route('tasks.index')->with('success', 'Задача успешно создана');
    }
}