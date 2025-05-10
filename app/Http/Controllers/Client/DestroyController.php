php
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Request;

class DestroyController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Client $client)
    {
        $this->clientService->deleteClient($client);

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.'); // Пример сообщения об успехе
    }
}