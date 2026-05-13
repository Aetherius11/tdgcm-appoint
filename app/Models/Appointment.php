<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    // 1. List the columns from your migration
    protected $fillable = [
        'appoint_no',
        'applicant_id',
        'principal_id',
        'purpose_id',
        'appointment_date', // <--- REQUIRED
        'time_slot_id',
        'status',
        'counter_number',
        'window_number',
        'checked_in_at',
        'called_at',
        'served_at',
        'served_by_user_id',
        'transaction_completed_at',
        'remarks',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'appointment_date' => 'date',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'transaction_completed_at' => 'datetime',
    ];

    // Boot method to generate unique appointment number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->appoint_no)) {
                // Generate unique appointment number like A-2026042701
                $date = date('Ymd');
                $lastAppointment = static::whereDate('created_at', today())
                    ->orderBy('id', 'desc')
                    ->first();
                $sequence = $lastAppointment ? (intval(substr($lastAppointment->appoint_no, -2)) + 1) : 1;
                $appointment->appoint_no = 'A-'.$date.str_pad($sequence, 2, '0', STR_PAD_LEFT);
            }
        });
    }

    // 2. Define the connection to the Applicant
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    // 3. Define the connection to the Principal
    public function principal(): BelongsTo
    {
        return $this->belongsTo(Principal::class);
    }

    // 4. Define the connection to the Purpose
    public function purpose(): BelongsTo
    {
        return $this->belongsTo(Purpose::class);
    }

    // 5. Define the connection to the Time Slot
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeAvailability::class, 'time_slot_id');
    }

    // 6. Define the connection to the User who served the appointment
    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by_user_id');
    }
}
