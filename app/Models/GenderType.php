<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenderType extends Model
{
    protected $table = 'gender_types';

    protected $fillable = ['name', 'slug', 'is_active', 'sort_order'];

    public function userDetails()
    {
        return $this->hasMany(UserDetail::class, 'gender_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
