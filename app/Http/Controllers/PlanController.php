<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Plan::class);
            
            $managers = User::where('role', 'manager')->get();
            $plans = Plan::whereIn('user_id', $managers->pluck('id'))->get()->keyBy('user_id');
            
            $totalMonthlyPlan = $plans->sum('monthly_plan');
            $totalDailyPlan = $plans->sum('daily_plan');

            return view('plans.index', compact('managers', 'plans', 'totalMonthlyPlan', 'totalDailyPlan'));
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке планов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке планов: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create', Plan::class);
        return view('plans.create');
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', Plan::class);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'target_amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $validated['user_id'] = auth()->id();
            Plan::create($validated);

            return redirect()->route('plans.index')->with('success', 'План успешно создан');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании плана', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при создании плана: ' . $e->getMessage());
        }
    }

    public function show(Plan $plan)
    {
        try {
            $this->authorize('view', $plan);
            return view('plans.show', compact('plan'));
        } catch (\Exception $e) {
            Log::error('Ошибка при просмотре плана', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при просмотре плана: ' . $e->getMessage());
        }
    }

    public function edit(Plan $plan)
    {
        try {
            $this->authorize('update', $plan);
            return view('plans.edit', compact('plan'));
        } catch (\Exception $e) {
            Log::error('Ошибка при редактировании плана', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при редактировании плана: ' . $e->getMessage());
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $plan = Plan::firstOrNew(['user_id' => $user->id]);
            $this->authorize('update', $plan);
            
            $validated = $request->validate([
                'monthly_plan' => 'required|numeric|min:0',
                'daily_plan' => 'required|numeric|min:0',
            ]);

            $plan->fill($validated);
            $plan->save();

            return redirect()->route('plans.index')->with('success', 'План успешно обновлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении плана', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при обновлении плана: ' . $e->getMessage());
        }
    }

    public function destroy(Plan $plan)
    {
        try {
            $this->authorize('delete', $plan);
            $plan->delete();
            return redirect()->route('plans.index')->with('success', 'План успешно удален');
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении плана', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при удалении плана: ' . $e->getMessage());
        }
    }
} 