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

    /**
     * Search for clients by name.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        $clients = Client::where('name', 'LIKE', "%{$query}%")
            ->limit(10) // Adjust the limit as needed
            ->get();
        return response()->json($clients);
    }
}