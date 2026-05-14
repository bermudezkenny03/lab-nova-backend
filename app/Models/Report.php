<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['report_request_id', 'file_path', 'file_name', 'report_file_type_id', 'generated_at'];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function reportRequest()
    {
        return $this->belongsTo(ReportRequest::class);
    }

    public function reportFileType()
    {
        return $this->belongsTo(ReportFileType::class, 'report_file_type_id');
    }
}
