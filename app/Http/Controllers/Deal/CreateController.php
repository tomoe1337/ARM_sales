<?php

namespace App\Http\Controllers\Deal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $clients = Client::where('user_id', Auth::id())->get();

        return view('deals.create', compact('clients'));
    }
}