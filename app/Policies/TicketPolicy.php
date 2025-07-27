<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authenticatable $user): bool
    {
        // If it's a Client, they can only view their own tickets
        if ($user instanceof Client) {
            return true; // Clients can view tickets (filtered by scopes)
        }

        // If it's a User (staff), check permissions
        if ($user instanceof User) {
            return $user->can('view_any_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authenticatable $user, Ticket $ticket): bool
    {
        // If it's a Client, they can only view their own tickets
        if ($user instanceof Client) {
            return $ticket->requester_id === $user->id;
        }

        // If it's a User (staff), check permissions
        if ($user instanceof User) {
            return $user->can('view_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable $user): bool
    {
        // Clients can create tickets
        if ($user instanceof Client) {
            return true;
        }

        // If it's a User (staff), check permissions
        if ($user instanceof User) {
            return $user->can('create_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authenticatable $user, Ticket $ticket): bool
    {
        // Clients cannot update tickets (only staff can)
        if ($user instanceof Client) {
            return false;
        }

        // If it's a User (staff), check permissions
        if ($user instanceof User) {
            return $user->can('update_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authenticatable $user, Ticket $ticket): bool
    {
        // Only Users (staff) can delete tickets
        if ($user instanceof User) {
            return $user->can('delete_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authenticatable $user): bool
    {
        // Only Users (staff) can delete tickets
        if ($user instanceof User) {
            return $user->can('delete_any_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(Authenticatable $user, Ticket $ticket): bool
    {
        // Only Users (staff) can force delete tickets
        if ($user instanceof User) {
            return $user->can('force_delete_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(Authenticatable $user): bool
    {
        // Only Users (staff) can force delete tickets
        if ($user instanceof User) {
            return $user->can('force_delete_any_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(Authenticatable $user, Ticket $ticket): bool
    {
        // Only Users (staff) can restore tickets
        if ($user instanceof User) {
            return $user->can('restore_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(Authenticatable $user): bool
    {
        // Only Users (staff) can restore tickets
        if ($user instanceof User) {
            return $user->can('restore_any_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(Authenticatable $user, Ticket $ticket): bool
    {
        // Only Users (staff) can replicate tickets
        if ($user instanceof User) {
            return $user->can('replicate_ticket');
        }

        return false;
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(Authenticatable $user): bool
    {
        // Only Users (staff) can reorder tickets
        if ($user instanceof User) {
            return $user->can('reorder_ticket');
        }

        return false;
    }
}
