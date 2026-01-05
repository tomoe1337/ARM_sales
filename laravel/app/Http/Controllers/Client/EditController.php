<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class EditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Client $client)
    {
        // Получаем пользователей только из своего отдела
        $employees = \App\Models\User::where('department_id', auth()->user()->department_id)->get();
        return view('clients.edit', compact('client', 'employees'));
    }
}