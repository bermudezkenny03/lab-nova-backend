<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationLog extends Model
{
    protected $fillable = ['reservation_id', 'user_id', 'reservation_log_action_id', 'description'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function action()
    {
        return $this->belongsTo(ReservationLogAction::class, 'reservation_log_action_id');
    }
}
