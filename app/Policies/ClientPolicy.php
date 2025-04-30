<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client)
    {
        return $user->id === $client->user_id
            ? Response::allow()
            : Response::deny('У вас нет доступа к этому клиенту');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client)
    {
        return $user->id === $client->user_id
            ? Response::allow()
            : Response::deny('У вас нет прав на редактирование этого клиента');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client)
    {
        return $user->id === $client->user_id
            ? Response::allow()
            : Response::deny('У вас нет прав на удаление этого клиента');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Client $client): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return false;
    }
}
