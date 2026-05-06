<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    protected string $module = 'categories';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->module, 'create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission($this->module, 'edit');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission($this->module, 'delete');
    }
}
