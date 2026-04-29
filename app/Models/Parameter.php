<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'parameter_type_id',
    ];

    public function parameterType()
    {
        return $this->belongsTo(ParameterType::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }
}