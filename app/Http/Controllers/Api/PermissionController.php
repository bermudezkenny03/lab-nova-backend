<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Controllers\Controller;
use App\Models\RoleModulePermission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;

class PermissionController extends Controller
{
    // Permission management methods
    public function index()
    {
        try {
            $roles = Role::with([
                'modulePermissions.module',
                'modulePermissions.permission'
            ])->get();

            $modules = Module::where('is_active', true)
                ->with('children')
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get();

            $permissions = Permission::all();

            return response()->json([
                'message' => 'Permissions retrieved successfully',
                'data' => [
                    'roles' => $roles,
                    'modules' => $modules,
                    'permissions' => $permissions,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving permissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Role permissions management methods
    public function getRolePermissions(Role $role)
    {
        try {
            $permissions = $role->modulePermissions()
                ->with(['module', 'permission'])
                ->get()
                ->groupBy('module.slug')
                ->map(function ($modulePermissions) {
                    return $modulePermissions
                        ->pluck('permission.slug')
                        ->toArray();
                });

            return response()->json([
                'message' => 'Role permissions retrieved successfully',
                'data' => [
                    'role' => $role,
                    'permissions' => $permissions,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving role permissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Bulk assign permissions to multiple roles
    public function assignPermissions(AssignPermissionsRequest $request, Role $role)
    {
        DB::beginTransaction();

        try {

            $validated = $request->validated();

            RoleModulePermission::where('role_id', $role->id)->delete();

            $insertData = [];
            $assignedCount = 0;
            $skippedModules = [];

            foreach ($validated['modules'] as $moduleData) {

                $moduleId = $moduleData['module_id'];
                $permissionIds = $moduleData['permission_ids'] ?? [];

                if (!empty($permissionIds)) {

                    foreach ($permissionIds as $permissionId) {

                        $insertData[] = [
                            'role_id' => $role->id,
                            'module_id' => $moduleId,
                            'permission_id' => $permissionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $assignedCount++;
                    }
                } else {

                    $module = Module::find($moduleId);
                    $skippedModules[] = $module ? $module->name : "Module ID: {$moduleId}";
                }
            }

            if (!empty($insertData)) {
                RoleModulePermission::insert($insertData);
            }

            DB::commit();

            // Clear permission cache for users of this role
            User::where('role_id', $role->id)->get()->each(function ($u) {
                $u->clearPermissionCache();
            });

            $message = "Permissions assigned successfully";

            if ($assignedCount > 0) {
                $message .= " ({$assignedCount} permissions assigned)";
            }

            if (!empty($skippedModules)) {
                $message .= ". Modules without permissions: " . implode(', ', $skippedModules);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'role_id' => $role->id,
                    'modules' => $role->getModulesWithInfo(),
                    'statistics' => [
                        'assigned_permissions' => $assignedCount,
                        'processed_modules' => count($validated['modules']),
                        'modules_without_permissions' => count($skippedModules),
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error assigning permissions', [
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error assigning permissions'
            ], 500);
        }
    }
}
