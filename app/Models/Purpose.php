<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Purpose extends Model
{
    protected $fillable = ['purpose_name', 'estimated_mins'];

    public function principal() {
        return $this->belongsTo(Principal::class, 'principal_id');
    }

    public function principals()
    {
        // Ensure the table name 'principal_purpose' matches your conjunction table
        return $this->belongsToMany(Principal::class, 'principal_purpose');
    }

    public function timeAvailabilities(): BelongsToMany
    {
        // Ensure the table name here matches exactly what you created in SQL
        return $this->belongsToMany(TimeAvailability::class, 'purpose_time_availability');
    }
}
