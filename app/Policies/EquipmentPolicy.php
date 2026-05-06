<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Equipment;

class EquipmentPolicy
{
    protected string $module = 'equipment';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->module, 'create');
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $user->hasPermission($this->module, 'edit');
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->hasPermission($this->module, 'delete');
    }
}
