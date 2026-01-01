<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class DestroyController extends Controller
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Plan $plan)
    {
        try {
            $this->authorize('delete', $plan);
            $this->planService->deletePlan($plan);

            return redirect()->route('plans.index')->with('success', 'План успешно удален');
        } catch (Exception $e) {
            Log::error('Ошибка при удалении плана', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при удалении плана: ' . $e->getMessage());
        }
    }
}