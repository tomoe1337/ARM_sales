php
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function __invoke(Request $request)
    {
        return view('clients.create');
    }
}