php
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;

class UpdateController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateClientRequest $request, Client $client)
    {
        $this->clientService->updateClient($client, $request->validated());

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully.');
    }
}