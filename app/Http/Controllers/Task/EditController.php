php
<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService; // Предполагаем, что у вас есть TaskService
use Illuminate\Http\Request;

class EditController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\View\View
     */
    public function __invoke(Task $task)
    {
        // Авторизация должна быть обработана либо через Policy, либо здесь.
        // Если используется authorizeResource в базовом контроллере,
        // убедитесь, что Policy корректно настроен для метода 'update'.
        // $this->authorize('update', $task);

        $employees = $this->taskService->getAssignableEmployees();

        return view('tasks.edit', compact('task', 'employees'));
    }
}