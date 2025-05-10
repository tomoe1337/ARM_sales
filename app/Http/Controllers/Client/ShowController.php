<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function __invoke(Request $request, Client $client)
    {
        return view('clients.show', compact('client'));
    }
}