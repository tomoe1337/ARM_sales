<?php

namespace App\Http\Controllers\Deal;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Services\DealService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestroyController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Deal $deal)
    {
        if (!$deal->canDelete(Auth::user())) {
            abort(403, 'У вас нет прав для удаления этой сделки');
        }

        $this->dealService->deleteDeal($deal);

        return redirect()->route('deals.index')->with('success', 'Сделка успешно удалена');
    }
}