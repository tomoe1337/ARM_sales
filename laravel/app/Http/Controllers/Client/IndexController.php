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
        $query = Client::query();

        if ($request->filled('first_contact_date_from')) {
            $query->whereDate('first_contact_date', '>=', $request->first_contact_date_from);
        }

        if ($request->filled('first_contact_date_to')) {
            $query->whereDate('first_contact_date', '<=', $request->first_contact_date_to);
        }

        $clients = $query->orderBy('first_contact_date', 'desc')->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
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