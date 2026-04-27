<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeAvailability extends Model
{
    protected $fillable = ['purpose_id', 'day_of_week', 'start_time', 'end_time', 'max_slots'];

    public function purpose() {
        return $this->belongsTo(Purpose::class);
    }
}
