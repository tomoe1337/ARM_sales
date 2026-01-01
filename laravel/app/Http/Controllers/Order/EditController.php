<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EditController extends Controller
{
    public function __invoke(Order $order)
    {
        $user = Auth::user();

        abort_unless($order->canEdit($user), 403, 'У вас нет прав для редактирования этого заказа');

        $clients = Client::where('user_id', auth()->id())->get();
        // Получаем пользователей только из своего отдела
        $users = User::where('department_id', Auth::user()->department_id)->get();

        return view('orders.edit', compact('order', 'clients', 'users'));
    }
}