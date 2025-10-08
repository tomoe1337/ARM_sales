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
        $employees = User::all();
        return view('clients.create',compact('employees'));
    }
}
