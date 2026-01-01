<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\TaskService;


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

        $user = Auth::user();
        $employees = $this->taskService->getAssignableEmployees($user);

        return view('tasks.create', compact('employees'));
    }
}