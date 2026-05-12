<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EquipmentImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'image_path',
        'image_name',
        'is_primary',
        'equipment_id',
    ];

    protected $appends = ['url'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function getUrlAttribute()
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public static function saveFiles($uploadedFiles, int $equipmentId): void
    {
        $files = is_array($uploadedFiles) ? $uploadedFiles : [$uploadedFiles];

        foreach ($files as $index => $uploadedFile) {
            $path = $uploadedFile->store(
                "equipment_images/{$equipmentId}",
                'public'
            );

            self::create([
                'image_path' => $path,
                'image_name' => $uploadedFile->getClientOriginalName(),
                'equipment_id' => $equipmentId,
                'is_primary' => $index === 0,
            ]);
        }
    }
}
