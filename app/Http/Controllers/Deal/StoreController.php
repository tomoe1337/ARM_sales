<?php

namespace App\Http\Controllers\Deal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDealRequest;
use App\Services\DealService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    public function __invoke(StoreDealRequest $request)
    {
        $this->dealService->createDeal($request->validated());

        return redirect()->route('deals.index')->with('success', 'Сделка успешно создана');
    }
}