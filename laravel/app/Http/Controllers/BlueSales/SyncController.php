<?php

namespace App\Http\Controllers\BlueSales;

use App\Http\Controllers\Controller;
use App\Services\BlueSales\BlueSalesSyncService;
use App\Services\BlueSales\BlueSalesApiService;
use App\Services\BlueSales\Synchronizers\CustomerSynchronizer;
use App\Services\BlueSales\Synchronizers\OrderSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncController extends Controller
{
    public function showSyncForm()
    {
        abort_unless(Auth::user()->isHead(), 403, 'Доступ запрещен');

        return view('bluesales.sync');
    }

    public function sync(Request $request)
    {
        abort_unless(Auth::user()->isHead(), 403, 'Доступ запрещен');

        $request->validate([
            'login' => 'required|string|email',
            'api_key' => 'required|string|min:10',
            'days_back' => 'required|integer|min:1|max:365'
        ]);

        // Создаем экземпляры сервисов (Dependency Injection)
        $apiService = new BlueSalesApiService(
            $request->login,
            $request->api_key
        );
        $customerSynchronizer = new CustomerSynchronizer();
        $orderSynchronizer = new OrderSynchronizer();
        
        $syncService = new BlueSalesSyncService(
            $apiService,
            $customerSynchronizer,
            $orderSynchronizer
        );

        $result = $syncService->syncDataForPeriod(
            $request->login,
            $request->api_key,
            $request->days_back
        );

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message'])->with('sync_result', $result);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}