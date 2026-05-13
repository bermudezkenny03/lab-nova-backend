<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRequestStatus extends Model
{
    protected $table = 'report_request_statuses';
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active'];
}
