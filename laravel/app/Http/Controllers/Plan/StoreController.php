<?php

namespace App\Http\Controllers\Plan;

use App\Http\Requests\StorePlanRequest;
use App\Services\PlanService;
use App\Http\Controllers\Controller;

class StoreController extends Controller
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StorePlanRequest $request)
    {
        $this->planService->createPlan($request->validated());

        return redirect()->route('plans.index')->with('success', 'План успешно создан');
    }
}