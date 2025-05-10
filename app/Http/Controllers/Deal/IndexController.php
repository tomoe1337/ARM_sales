<?php

namespace App\Http\Controllers\Deal;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        $query = Deal::with(['client', 'user']);

        if (!$user->isHead()) {
            $query->where('user_id', $user->id);
        }

        $deals = $query->get();

        return view('deals.index', compact('deals'));
    }
}