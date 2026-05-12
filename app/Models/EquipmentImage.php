<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
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

    // Relationships
    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    // Accessors
    public function getUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    // Static methods for file handling
    public static function saveFiles(array|UploadedFile|null $uploadedFiles, int $equipmentId): void
    {
        if (! $uploadedFiles) {
            return;
        }

        $files = is_array($uploadedFiles) ? $uploadedFiles : [$uploadedFiles];

        foreach ($files as $index => $uploadedFile) {
            if (! $uploadedFile instanceof UploadedFile) {
                continue;
            }

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

    // Replaces existing files with new ones
    public static function replaceFiles(array|UploadedFile|null $uploadedFiles, int $equipmentId): void
    {
        if (! $uploadedFiles) {
            return;
        }

        self::deleteFiles($equipmentId);

        self::saveFiles($uploadedFiles, $equipmentId);
    }

    // Deletes files from storage and database
    public static function deleteFiles(int $equipmentId): void
    {
        $images = self::where('equipment_id', $equipmentId)->get();

        foreach ($images as $image) {
            $image->delete();
        }
    }

    // Model event hooks
    protected static function booted(): void
    {
        static::deleting(function (EquipmentImage $image): void {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        });
    }
}
