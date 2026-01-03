<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CreateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $selectedClient = null;
        $selectedClientId = $request->query('client_id');
        if ($selectedClientId) {
            $selectedClient = Client::find($selectedClientId);
        }

        // Получаем клиентов с bluesales_id согласно OrganizationScope
        // OrganizationScope автоматически применит фильтрацию по департаменту/организации
        $clients = Client::whereNotNull('bluesales_id')->get();
        
        // Получаем пользователей только из своего отдела
        $users = User::where('department_id', Auth::user()->department_id)->get();

        return view('orders.create', compact('clients', 'selectedClient', 'selectedClientId', 'users'));
    }
}