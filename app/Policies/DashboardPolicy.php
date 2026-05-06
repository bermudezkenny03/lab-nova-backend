<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dashboard;

class DashboardPolicy
{
    /**
     * Determina si un usuario puede ver el dashboard.
     * Regla: si tiene permiso explícito en 'dashboard' O tiene 'view' en cualquiera
     * de los módulos relevantes (reports, reservations, equipment, users, categories).
     */
    public function view(User $user): bool
    {
        if (method_exists($user, 'hasPermission')) {
            // Allow if explicit dashboard permission or if user has view on any relevant module
            if ($user->hasPermission('dashboard', 'view')) {
                return true;
            }

            $relevant = ['reports', 'reservations', 'equipment', 'users', 'categories'];
            foreach ($relevant as $r) {
                if ($user->hasPermission($r, 'view')) {
                    return true;
                }
            }
        }

        return false;
    }
}
