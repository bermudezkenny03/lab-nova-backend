<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationStatus extends Model
{
    protected $table = 'reservation_statuses';
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active'];
    public $timestamps = true;
}
