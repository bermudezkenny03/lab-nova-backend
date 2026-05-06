<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\Category;
use App\Models\Report;
use App\Models\ReportRequest;
use App\Models\User;
use App\Models\Dashboard;
use App\Policies\EquipmentPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ReportRequestPolicy;
use App\Policies\UserPolicy;
use App\Policies\DashboardPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Equipment::class => EquipmentPolicy::class,
        Reservation::class => ReservationPolicy::class,
        Category::class => CategoryPolicy::class,
        Report::class => ReportPolicy::class,
        ReportRequest::class => ReportRequestPolicy::class,
        User::class => UserPolicy::class,
        Dashboard::class => DashboardPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Optionally, a super-admin bypass
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isStaff') && $user->isStaff() && $user->role->name === 'Super Admin') {
                return true;
            }
            return null;
        });
    }
}
