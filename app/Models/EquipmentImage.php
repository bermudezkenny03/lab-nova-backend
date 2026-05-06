<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentImage extends Model
{
    use SoftDeletes;

    protected $fillable = ['image_path', 'image_name', 'is_primary', 'equipment_id'];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
