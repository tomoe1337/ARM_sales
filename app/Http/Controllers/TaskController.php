<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Task::class, 'task');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = auth()->user()->isHead() 
            ? User::where('id', '!=', auth()->id())->get()
            : collect();
        return view('tasks.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'assignee_id' => 'required|exists:users,id',
        ]);

        $user = auth()->user();
        $validated['user_id'] = $user->id;
        
        // Если пользователь не руководитель, он может быть только исполнителем своей задачи
        if (!$user->isHead()) {
            $validated['assignee_id'] = $user->id;
        }

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Задача успешно создана');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $employees = auth()->user()->isHead() 
            ? User::where('id', '!=', auth()->id())->get()
            : collect();
        return view('tasks.edit', compact('task', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'assignee_id' => 'required|exists:users,id',
        ]);

        $user = auth()->user();
        
        // Если пользователь не руководитель, он может быть только исполнителем своей задачи
        if (!$user->isHead()) {
            $validated['assignee_id'] = $user->id;
        }

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Задача успешно обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Задача успешно удалена');
    }
}
