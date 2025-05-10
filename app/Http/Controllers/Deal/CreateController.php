<?php

namespace App\Http\Controllers\Deal;

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
        $selectedClientId = $request->query('client_id');
        $clients = Client::where('user_id', Auth::id())->get();
        $users = User::all(); // Получаем всех пользователей

        return view('deals.create', compact('clients', 'selectedClientId', 'users'));
    }
}