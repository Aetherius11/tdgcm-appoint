<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    protected $fillable = ['principal_id', 'purpose_name', 'estimated_mins'];

    public function principal() {
        return $this->belongsTo(Principal::class, 'principal_id');
    }
}
