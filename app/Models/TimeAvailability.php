<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeAvailability extends Model
{
    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'max_slots',
        'purpose_id',
    ];

    // This tells Laravel how to find the "Purpose"
    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }
}
