<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    // 1. List the columns from your migration
    protected $fillable = [
        'appoint_id',
        'applicant_id',
        'purpose_id',
        'appointment_date',
        'time_slot_id',
        'status',
        'remarks',
    ];

    // 2. Define the connection to the Applicant
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    // 3. Define the connection to the Purpose
    public function purpose(): BelongsTo
    {
        return $this->belongsTo(Purpose::class);
    }
}