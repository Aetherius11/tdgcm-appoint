<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentWindowLog extends Model
{
    protected $fillable = [
        'appointment_id', 'window_id', 'window_name', 'appoint_no',
        'called_at', 'served_at', 'ended_at', 'end_type',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'ended_at'  => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
