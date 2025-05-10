<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function __invoke()
    {
        $this->authorize('create', \App\Models\Task::class);

        $employees = $this->taskService->getAssignableEmployees();

        return view('tasks.create', compact('employees'));
    }
}