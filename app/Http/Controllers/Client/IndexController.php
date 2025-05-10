<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function __invoke(Request $request): View
    {
        $deals = Client::all();

        return view('clients.index', compact('deals'));
    }
}