<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

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

        return view('tasks.index', compact('tasks'));
    }
}