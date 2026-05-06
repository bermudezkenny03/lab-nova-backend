<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    protected string $module = 'users';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function view(User $user, User $model): bool
    {
        // allow viewing own profile
        if ($user->id === $model->id) return true;
        return $user->hasPermission($this->module, 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->module, 'create');
    }

    public function update(User $user, User $model): bool
    {
        // users can update own profile; others need edit permission
        if ($user->id === $model->id) return true;
        return $user->hasPermission($this->module, 'edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission($this->module, 'delete');
    }
}
