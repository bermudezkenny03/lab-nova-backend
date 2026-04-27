<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Module model representing application modules for permissions and navigation
class Module extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'icon', 'route', 'parent_id', 'is_active', 'sort_order', 'show_in_sidebar'];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }
    // Get all child modules (sub-modules) of this module
    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id')->orderBy('sort_order');
    }
    // Get all permissions associated with this module through the RoleModulePermission pivot table
    public function rolePermissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }
    // Scope to filter only active modules
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    // Scope to order modules by their sort_order field
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
    // Scope to filter only parent modules (modules without a parent_id)
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
