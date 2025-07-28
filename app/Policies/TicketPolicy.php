<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Client $user): bool
    {
        if ($user instanceof Client) {
            return true; // Clients can view their own tickets
        }
        
        return $user->can('view_any_ticket');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Client $user, Ticket $ticket): bool
    {
        if ($user instanceof Client) {
            return $ticket->requester_id === $user->id;
        }
        
        return $user->can('view_ticket');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Client $user): bool
    {
        if ($user instanceof Client) {
            return true; // Clients can create tickets
        }
        
        return $user->can('create_ticket');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Client $user, Ticket $ticket): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot update tickets
        }
        
        return $user->can('update_ticket');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Client $user, Ticket $ticket): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot delete tickets
        }
        
        return $user->can('delete_ticket');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User|Client $user): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot bulk delete tickets
        }
        
        return $user->can('delete_any_ticket');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User|Client $user, Ticket $ticket): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot force delete tickets
        }
        
        return $user->can('force_delete_ticket');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User|Client $user): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot bulk force delete tickets
        }
        
        return $user->can('force_delete_any_ticket');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User|Client $user, Ticket $ticket): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot restore tickets
        }
        
        return $user->can('restore_ticket');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User|Client $user): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot bulk restore tickets
        }
        
        return $user->can('restore_any_ticket');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User|Client $user, Ticket $ticket): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot replicate tickets
        }
        
        return $user->can('replicate_ticket');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User|Client $user): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot reorder tickets
        }
        
        return $user->can('reorder_ticket');
    }
}
