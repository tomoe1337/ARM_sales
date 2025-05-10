<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService; // Предполагаем, что у вас есть TaskService
use Illuminate\Support\Facades\Auth; // Импортируем фасад Auth

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

        $user = Auth::user(); // Получаем текущего пользователя
        $employees = $this->taskService->getAssignableEmployees($user); // Передаем пользователя в метод

        return view('tasks.edit', compact('task', 'employees'));
    }
}