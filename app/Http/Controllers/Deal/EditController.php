<?php

namespace App\Http\Controllers\Deal;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EditController extends Controller
{
    public function __invoke(Deal $deal)
    {
        $user = Auth::user();

        abort_unless($deal->canEdit($user), 403, 'У вас нет прав для редактирования этой сделки');

        $clients = Client::where('user_id', auth()->id())->get();
        $users = User::all(); // Получаем всех пользователей

        return view('deals.edit', compact('deal', 'clients', 'users'));
    }
}
