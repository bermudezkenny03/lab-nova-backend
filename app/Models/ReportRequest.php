<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRequest extends Model
{
    protected $fillable = ['user_id', 'type', 'start_date', 'end_date', 'status', 'filters'];

    protected $casts = [
        'filters' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
