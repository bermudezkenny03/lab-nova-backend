<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipments';

    protected $fillable = [
        'category_id',
        'equipment_status_id',
        'name',
        'code',
        'description',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    // Relationships
    public function images()
    {
        return $this->hasMany(EquipmentImage::class, 'equipment_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function status()
    {
        return $this->belongsTo(EquipmentStatus::class, 'equipment_status_id');
    }

    // Scope to filter only active equipment
    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // Model event hooks
    protected static function booted(): void
    {
        static::creating(function (Equipment $equipment): void {
            if (! $equipment->code) {
                $equipment->code = self::generateNextCode();
            }
        });
    }

    // Static method to generate the next sequential code for equipment
    public static function generateNextCode(): string
    {
        $lastEquipment = self::withTrashed()
            ->whereNotNull('code')
            ->where('code', 'like', 'EQ-%')
            ->orderByDesc('id')
            ->first();

        if (! $lastEquipment) {
            return 'EQ-000001';
        }

        $lastNumber = (int) str_replace('EQ-', '', $lastEquipment->code);

        $nextNumber = $lastNumber + 1;

        return 'EQ-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
