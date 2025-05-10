<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use App\Models\Plan;

class UpdateController extends Controller
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->planService->updatePlan($plan, $request->validated());

        return redirect()->route('plans.index')->with('success', 'План успешно обновлен');
    }
}