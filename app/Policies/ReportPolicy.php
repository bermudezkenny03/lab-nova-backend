<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;

class ReportPolicy
{
    protected string $module = 'reports';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function view(User $user, Report $report): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->module, 'create');
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->hasPermission($this->module, 'delete');
    }
}
