<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'equipment_id', 'start_time', 'end_time', 'reservation_status_id', 'notes', 'rejection_reason', 'approved_by', 'approved_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs()
    {
        return $this->hasMany(ReservationLog::class);
    }

    public function reservationStatus()
    {
        return $this->belongsTo(ReservationStatus::class, 'reservation_status_id');
    }

    public function getStatusAttribute()
    {
        return $this->reservationStatus?->slug;
    }

    public function setStatusAttribute($value)
    {
        $status = ReservationStatus::where('slug', $value)->first();
        if ($status) {
            $this->attributes['reservation_status_id'] = $status->id;
        }
    }
}
