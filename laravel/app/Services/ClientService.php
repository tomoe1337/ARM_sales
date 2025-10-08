<?php

namespace App\Services;

use App\Models\Client;

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