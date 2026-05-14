<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReportRequest;

class ReportRequestPolicy
{
    protected string $module = 'report-requests';

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->module, 'view');
    }

    public function view(User $user, ReportRequest $reportRequest): bool
    {
        // owners can view their requests
        if ($reportRequest->user_id === $user->id) return true;
        return $user->hasPermission($this->module, 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->module, 'create');
    }

    public function update(User $user, ReportRequest $reportRequest): bool
    {
        return $user->hasPermission($this->module, 'edit');
    }

    public function delete(User $user, ReportRequest $reportRequest): bool
    {
        return $user->hasPermission($this->module, 'delete');
    }

    public function generate(User $user, ReportRequest $reportRequest): bool
    {
        return $user->hasPermission($this->module, 'edit');
    }
}
