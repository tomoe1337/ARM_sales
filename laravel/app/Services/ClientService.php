<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ClientService
{
    /**
     * Create a new client.
     *
     * @param array $data The data for the new client.
     * @return Client The created client object.
     */
    public function createClient(array $data): Client
    {
        // Если organization_id и department_id не указаны, берем из user_id или текущего пользователя
        if (!isset($data['organization_id']) || !isset($data['department_id'])) {
            $user = null;
            if (isset($data['user_id'])) {
                $user = User::find($data['user_id']);
            } elseif (Auth::check()) {
                $user = Auth::user();
            }
            
            if ($user) {
                $data['organization_id'] = $data['organization_id'] ?? $user->organization_id;
                $data['department_id'] = $data['department_id'] ?? $user->department_id;
            }
        }
        
        return Client::create($data);
    }

    /**
     * Update an existing client.
     *
     * @param Client $client The client to update.
     * @param array $data The data to update the client with.
     * @return Client The updated client object.
     */
    public function updateClient(Client $client, array $data): Client
    {
        $client->update($data);
        return $client;
    }

    /**
     * Delete a client.
     *
     * @param Client $client The client to delete.
     * @return bool True if the client was deleted, false otherwise.
     */
    public function deleteClient(Client $client): bool
    {
        return $client->delete();
    }
}