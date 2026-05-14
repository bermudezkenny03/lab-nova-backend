<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reservation;

class ReservationPolicy
{
    protected string $module = 'reservations';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function view(User $user, Reservation $reservation): bool
    {
        // allow if owner or has view permission
        if ($reservation->user_id === $user->id) return true;
        return $user->hasPermission($this->module, 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->module, 'create');
    }

    public function update(User $user, Reservation $reservation): bool
    {
        if ($reservation->user_id === $user->id && $reservation->status === 'pending') return true;
        return $user->hasPermission($this->module, 'edit');
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        if ($reservation->user_id === $user->id && in_array($reservation->status, ['pending','rejected'])) return true;
        return $user->hasPermission($this->module, 'delete');
    }

    public function approve(User $user, Reservation $reservation): bool
    {
        return $user->hasPermission($this->module, 'edit');
    }

    public function reject(User $user, Reservation $reservation): bool
    {
        return $user->hasPermission($this->module, 'edit');
    }
}
