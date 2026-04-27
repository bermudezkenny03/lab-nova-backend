<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Role model representing user roles in the application, which can have multiple permissions for different modules
class Role extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description'];

    // Define the relationship to the User model (one role can have many users)
    public function users()
    {
        return $this->hasMany(User::class);
    }
    // Define the relationship to RoleModulePermission pivot table (one role can have many module permissions)    
    public function modulePermissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }
    // Scope to order roles by their ID in descending order
    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }
    // Check if the role has permissions for a specific module by its slug, considering only active modules
    public function hasModule($moduleSlug)
    {
        return $this->modulePermissions()
            ->whereHas('module', function ($query) use ($moduleSlug) {
                $query->where('slug', $moduleSlug)->where('is_active', true);
            })
            ->exists();
    }
    // Get a list of unique module slugs that this role has permissions for, filtering only active modules
    public function getModules()
    {
        return $this->modulePermissions()
            ->with('module')
            ->whereHas('module', function ($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->pluck('module.slug')
            ->unique()
            ->values()
            ->toArray();
    }
    // Get detailed information about the modules this role has permissions for, including parent-child relationships and permissions for each module
    public function getModulesWithInfo()
    {
        $userModulePermissions = $this->getUserModulePermissions();
        $parentModules = $this->getActiveParentModules();

        return $parentModules
            ->map(fn($parent) => $this->buildParentModuleData($parent, $userModulePermissions))
            ->filter() // Remover nulls
            ->sortBy('sort_order')
            ->values()
            ->toArray();
    }
    // Helper method to get the role's module permissions grouped by module slug, including only active modules
    private function getUserModulePermissions()
    {
        return $this->modulePermissions()
            ->with(['module', 'permission'])
            ->whereHas(
                'module',
                fn($query) =>
                $query->where('is_active', true)->whereNotNull('parent_id')
            )
            ->get()
            ->groupBy('module.slug');
    }
    // Helper method to get all active parent modules with their children, filtering only those that have permissions for the role
    private function getActiveParentModules()
    {
        return Module::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }
    // Helper method to build the data structure for a parent module, including its visible children and their permissions, only if the parent has visible children 
    private function buildParentModuleData($parentModule, $userModulePermissions)
    {
        $visibleChildren = $this->getVisibleChildren($parentModule, $userModulePermissions);

        if ($visibleChildren->isEmpty()) {
            return null; // Yes, this will remove parent modules that have no visible children (i.e., no permissions for the role)
        }

        return [
            'slug' => $parentModule->slug,
            'name' => $parentModule->name,
            'icon' => $parentModule->icon,
            'route' => $parentModule->route,
            'permissions' => [],
            'sort_order' => $parentModule->sort_order,
            'children' => $visibleChildren->sortBy('sort_order')->values()->toArray()
        ];
    }
    //  Helper method to get the visible children of a parent module based on the role's permissions, returning only those children that the role has permissions for
    private function getVisibleChildren($parentModule, $userModulePermissions)
    {
        return $parentModule->children
            ->filter(
                fn($child) =>
                $userModulePermissions->has($child->slug)
            )
            ->map(fn($child) => [
                'slug' => $child->slug,
                'name' => $child->name,
                'icon' => $child->icon,
                'route' => $child->route,
                'permissions' => $userModulePermissions[$child->slug]->pluck('permission.slug')->toArray(),
                'sort_order' => $child->sort_order,
                'show_in_sidebar' => $child->show_in_sidebar,
            ]);
    }
}
