<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function __invoke(Request $request)
    {
        // Получаем пользователей только из своего отдела
        $employees = User::where('department_id', auth()->user()->department_id)->get();
        return view('clients.create',compact('employees'));
    }
}
