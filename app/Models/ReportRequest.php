<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'report_request_type_id',
        'start_date',
        'end_date',
        'report_request_status_id',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportRequestType()
    {
        return $this->belongsTo(ReportRequestType::class, 'report_request_type_id');
    }

    public function reportRequestStatus()
    {
        return $this->belongsTo(ReportRequestStatus::class, 'report_request_status_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
