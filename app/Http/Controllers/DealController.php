<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Deal::class, 'deal');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login');
            }

            $query = Deal::with(['client', 'user']);
            
            if (!$user->isHead()) {
                $query->where('user_id', $user->id);
            }

            $deals = $query->get();
            
            return view('deals.index', compact('deals'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке сделок');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('user_id', auth()->id())->get();
        return view('deals.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:new,in_progress,won,lost',
            'closed_at' => 'nullable|date',
            'client_id' => 'required|exists:clients,id',
        ]);

        $validated['user_id'] = auth()->id();
        Deal::create($validated);

        return redirect()->route('deals.index')->with('success', 'Сделка успешно создана');
    }

    /**
     * Display the specified resource.
     */
    public function show(Deal $deal)
    {
        if (!$deal->canView(auth()->user())) {
            abort(403, 'У вас нет прав для просмотра этой сделки');
        }
        
        return view('deals.show', compact('deal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Deal $deal)
    {
        if (!$deal->canEdit(auth()->user())) {
            abort(403, 'У вас нет прав для редактирования этой сделки');
        }
        
        return view('deals.edit', compact('deal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Deal $deal)
    {
        if (!$deal->canEdit(auth()->user())) {
            abort(403, 'У вас нет прав для редактирования этой сделки');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:new,in_progress,won,lost',
            'closed_at' => 'nullable|date',
            'client_id' => 'required|exists:clients,id',
        ]);

        $deal->update($validated);

        return redirect()->route('deals.index')->with('success', 'Сделка успешно обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deal $deal)
    {
        if (!$deal->canDelete(auth()->user())) {
            abort(403, 'У вас нет прав для удаления этой сделки');
        }

        $deal->delete();
        return redirect()->route('deals.index')->with('success', 'Сделка успешно удалена');
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:open,won,lost',
            'closed_at' => 'nullable|date',
            'client_id' => 'required|exists:clients,id',
        ];
    }
}
