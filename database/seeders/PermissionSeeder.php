<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Module;
use App\Models\Permission;
use App\Models\RoleModulePermission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        RoleModulePermission::truncate();
        Module::truncate();
        Permission::truncate();
        Role::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = [
            'Super Admin' => 'Full system access',
            'Admin' => 'Manages users, equipment, reservations, and reports',
            'Lab Manager' => 'Manages laboratory resources and reservations',
            'Teacher' => 'Creates reservations and views reports',
            'Student' => 'Creates and views their reservations',
        ];

        foreach ($roles as $name => $description) {
            Role::create([
                'name' => $name,
                'description' => $description,
                'status' => 1,
            ]);
        }

        $superAdmin = Role::where('name', 'Super Admin')->first();
        $admin = Role::where('name', 'Admin')->first();
        $labManager = Role::where('name', 'Lab Manager')->first();
        $teacher = Role::where('name', 'Teacher')->first();
        $student = Role::where('name', 'Student')->first();

        $dashboard = Module::create([
            'name' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'mdi-view-dashboard',
            'route' => '/dashboard',
            'parent_id' => null,
            'is_active' => 1,
            'sort_order' => 1,
            'show_in_sidebar' => 1,
        ]);

        $resourceManagement = Module::create([
            'name' => 'Resources',
            'slug' => 'catalog-management',
            'icon' => 'mdi-laptop',
            'route' => null,
            'parent_id' => null,
            'is_active' => 1,
            'sort_order' => 2,
            'show_in_sidebar' => 1,
        ]);

        $reservationManagement = Module::create([
            'name' => 'Scheduling',
            'slug' => 'reservation-management',
            'icon' => 'mdi-calendar-clock',
            'route' => null,
            'parent_id' => null,
            'is_active' => 1,
            'sort_order' => 3,
            'show_in_sidebar' => 1,
        ]);

        $reportManagement = Module::create([
            'name' => 'Reporting',
            'slug' => 'report-management',
            'icon' => 'mdi-file-chart',
            'route' => null,
            'parent_id' => null,
            'is_active' => 1,
            'sort_order' => 4,
            'show_in_sidebar' => 1,
        ]);

        $accessManagement = Module::create([
            'name' => 'Access Control',
            'slug' => 'access-management',
            'icon' => 'mdi-shield-account',
            'route' => null,
            'parent_id' => null,
            'is_active' => 1,
            'sort_order' => 5,
            'show_in_sidebar' => 1,
        ]);

        $categories = Module::create([
            'name' => 'Categories',
            'slug' => 'categories',
            'icon' => 'mdi-shape',
            'route' => '/categories',
            'parent_id' => $resourceManagement->id,
            'is_active' => 1,
            'sort_order' => 1,
            'show_in_sidebar' => 1,
        ]);

        $equipment = Module::create([
            'name' => 'Equipment',
            'slug' => 'equipment',
            'icon' => 'mdi-desktop-classic',
            'route' => '/equipment',
            'parent_id' => $resourceManagement->id,
            'is_active' => 1,
            'sort_order' => 2,
            'show_in_sidebar' => 1,
        ]);

        $reservations = Module::create([
            'name' => 'Bookings',
            'slug' => 'reservations',
            'icon' => 'mdi-calendar',
            'route' => '/reservations',
            'parent_id' => $reservationManagement->id,
            'is_active' => 1,
            'sort_order' => 1,
            'show_in_sidebar' => 1,
        ]);

        $reservationLogs = Module::create([
            'name' => 'History',
            'slug' => 'reservation-logs',
            'icon' => 'mdi-history',
            'route' => '/reservation-logs',
            'parent_id' => $reservationManagement->id,
            'is_active' => 1,
            'sort_order' => 2,
            'show_in_sidebar' => 1,
        ]);

        $reportRequests = Module::create([
            'name' => 'Requests',
            'slug' => 'report-requests',
            'icon' => 'mdi-file-send',
            'route' => '/report-requests',
            'parent_id' => $reportManagement->id,
            'is_active' => 1,
            'sort_order' => 1,
            'show_in_sidebar' => 1,
        ]);

        $reports = Module::create([
            'name' => 'Analytics',
            'slug' => 'reports',
            'icon' => 'mdi-file-chart',
            'route' => '/reports',
            'parent_id' => $reportManagement->id,
            'is_active' => 1,
            'sort_order' => 2,
            'show_in_sidebar' => 1,
        ]);

        $users = Module::create([
            'name' => 'Users',
            'slug' => 'users',
            'icon' => 'mdi-account-group',
            'route' => '/users',
            'parent_id' => $accessManagement->id,
            'is_active' => 1,
            'sort_order' => 1,
            'show_in_sidebar' => 1,
        ]);

        $rolesModule = Module::create([
            'name' => 'Roles',
            'slug' => 'roles',
            'icon' => 'mdi-badge-account',
            'route' => '/roles',
            'parent_id' => $accessManagement->id,
            'is_active' => 1,
            'sort_order' => 2,
            'show_in_sidebar' => 1,
        ]);

        $permissionList = collect([
            ['name' => 'View', 'slug' => 'view'],
            ['name' => 'Create', 'slug' => 'create'],
            ['name' => 'Edit', 'slug' => 'edit'],
            ['name' => 'Delete', 'slug' => 'delete'],
        ])->map(fn($permission) => Permission::create($permission));

        $assignPermissions = function ($role, $modules, $permissions) {
            foreach ($modules as $module) {
                foreach ($permissions as $permission) {
                    RoleModulePermission::create([
                        'role_id' => $role->id,
                        'module_id' => $module->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        };

        $assignPermissions($superAdmin, Module::all(), $permissionList);

        $assignPermissions($admin, [
            $dashboard,
            $categories,
            $equipment,
            $reservations,
            $reservationLogs,
            $reportRequests,
            $reports,
            $users,
            $rolesModule,
        ], $permissionList);

        $assignPermissions($labManager, [
            $dashboard,
            $categories,
            $equipment,
            $reservations,
            $reservationLogs,
            $reportRequests,
            $reports,
        ], $permissionList);

        $assignPermissions($teacher, [
            $dashboard,
            $reservations,
            $reportRequests,
            $reports,
        ], $permissionList->whereIn('slug', ['view', 'create']));

        $assignPermissions($student, [
            $dashboard,
            $reservations,
        ], $permissionList->whereIn('slug', ['view', 'create']));

        $this->command->info('✅ Roles, modules, and permissions created successfully');
    }
}
