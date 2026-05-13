<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;

    // This line tells Laravel to use your actual table name
    protected $table = 'office_departments'; 

    protected $fillable = [
        'name',
        'personal_team',
    ];
}