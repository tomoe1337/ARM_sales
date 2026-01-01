<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService; // Предполагаем наличие TaskService

class DestroyController extends Controller
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Task $task)
    {
        // Авторизация может быть вынесена в Policy, но пока оставляем здесь для примера
        // $this->authorize('delete', $task);

        try {
            $this->taskService->deleteTask($task);

            return redirect()->route('tasks.index')->with('success', 'Задача успешно удалена');
        } catch (\Exception $e) {
            // Логирование ошибки, как в оригинальном контроллере
            \Illuminate\Support\Facades\Log::error('Ошибка при удалении задачи', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Произошла ошибка при удалении задачи: ' . $e->getMessage());
        }
    }
}