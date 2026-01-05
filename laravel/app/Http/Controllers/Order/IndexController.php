<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        $query = Order::with(['client', 'user']);

        if (!$user->isHead()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('created_at_from')) {
            $query->whereDate('created_at', '>=', $request->created_at_from);
        }

        if ($request->filled('created_at_to')) {
            $query->whereDate('created_at', '<=', $request->created_at_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }
}