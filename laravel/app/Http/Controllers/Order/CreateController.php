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

        $clients = Client::where('user_id', Auth::id())->get();
        $users = User::all(); // Получаем всех пользователей

        return view('orders.create', compact('clients', 'selectedClient', 'selectedClientId', 'users'));
    }
}