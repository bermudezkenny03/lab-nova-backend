<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationLogAction extends Model
{
    protected $table = 'reservation_log_actions';

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'sort_order'];
}
