<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email', 'phone'];

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}
