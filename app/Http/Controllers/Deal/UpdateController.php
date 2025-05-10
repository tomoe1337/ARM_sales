php
<?php

namespace App\Http\Controllers\Deal;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;
use App\Services\DealService;
use Illuminate\Support\Facades\Auth;

class UpdateController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateDealRequest $request, Deal $deal)
    {
        if (! $deal->canEdit(Auth::user())) {
             abort(403, 'У вас нет прав для редактирования этой сделки');
        }

        $this->dealService->updateDeal($deal, $request->validated());

        return redirect()->route('deals.show', $deal)->with('success', 'Сделка успешно обновлена');
    }
}