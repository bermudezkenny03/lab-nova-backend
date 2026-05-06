<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'status'];

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
