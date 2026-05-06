<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['report_request_id', 'file_path', 'file_name', 'file_type', 'generated_at'];

    public function reportRequest()
    {
        return $this->belongsTo(ReportRequest::class);
    }
}
