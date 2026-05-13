<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRequestType extends Model
{
    protected $table = 'report_request_types';
    protected $fillable = ['name', 'slug', 'description', 'sort_order'];
}
