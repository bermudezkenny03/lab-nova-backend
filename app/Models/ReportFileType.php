<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportFileType extends Model
{
    protected $table = 'report_file_types';
    protected $fillable = ['name', 'slug', 'extension', 'sort_order'];
}
