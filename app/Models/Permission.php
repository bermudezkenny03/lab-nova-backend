<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Permission model representing individual permissions that can be assigned to roles for specific modules
class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug'];

    // Define the relationship to RoleModulePermission pivot table
    public function roleModules()
    {
        return $this->hasMany(RoleModulePermission::class);
    }
}
