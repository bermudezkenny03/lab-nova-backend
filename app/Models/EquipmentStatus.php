<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentStatus extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'equipment_status_id');
    }
}
