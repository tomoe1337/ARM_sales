<?php

namespace App\Http\Controllers\Deal;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Deal $deal)
    {
        // The instruction asks for the same logic that is already present, so no changes are needed.
        return view('deals.show', compact('deal'));
    }
}