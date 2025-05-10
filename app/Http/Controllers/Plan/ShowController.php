php
<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShowController extends Controller
{
    public function __invoke(Request $request, Plan $plan)
    {
        try {
            $this->authorize('view', $plan);

            // If business logic were needed, it would be called from a Service here.
            // For showing a plan, fetching is handled by route model binding,
            // so no explicit service call is necessary unless complex data transformation is required.

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
}