<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class EditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Plan $plan)
    {
        try {
        $this->authorize('update', $plan);
        return view('plans.edit', compact('plan'));
        } catch (Exception $e) {
        Log::error('Ошибка при редактировании плана', [
        'plan_id' => $plan->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Произошла ошибка при редактировании плана: ' . $e->getMessage());
        }
    }
}