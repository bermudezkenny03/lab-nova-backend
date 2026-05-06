<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Permission;
use App\Models\Module;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\User;

class PermissionsBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_permission_map_and_has_permission()
    {
        $permView = Permission::create(['name' => 'Ver', 'slug' => 'view']);
        $permCreate = Permission::create(['name' => 'Crear', 'slug' => 'create']);
        $permEdit = Permission::create(['name' => 'Editar', 'slug' => 'edit']);

        $module = Module::create([
            'name' => 'Equipos',
            'slug' => 'equipment',
            'is_active' => 1,
            'sort_order' => 1,
        ]);

        $role = Role::create(['name' => 'Tester', 'description' => 'Test role', 'status' => 1]);

        RoleModulePermission::create(['role_id' => $role->id, 'module_id' => $module->id, 'permission_id' => $permView->id]);
        RoleModulePermission::create(['role_id' => $role->id, 'module_id' => $module->id, 'permission_id' => $permCreate->id]);

        $user = User::factory()->create(['role_id' => $role->id, 'email' => 'test@example.com']);

        $this->assertTrue($user->hasPermission('equipment', 'view'));
        $this->assertTrue($user->hasPermission('equipment', 'create'));
        $this->assertFalse($user->hasPermission('equipment', 'edit'));

        $map = $user->getPermissionMap(['equipment']);
        $this->assertArrayHasKey('equipment', $map);
        $this->assertTrue($map['equipment']['view']);
        $this->assertTrue($map['equipment']['create']);
        $this->assertFalse($map['equipment']['edit']);
    }
}
