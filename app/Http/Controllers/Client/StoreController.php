php
<?php

namespace App\Http\Controllers\Client;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Services\ClientService;

class StoreController
{
    protected $clientService;

    /**
     * Create a new controller instance.
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreClientRequest $request) 
 {
        $validatedData = $request->validated(); 

        $this->clientService->createClient($validatedData);

        return redirect()->route('clients.index')->with('success', 'Клиент успешно создан.');
    }
}