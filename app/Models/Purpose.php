<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    protected $fillable = ['purpose_name', 'estimated_mins'];

    public function principal() {
        return $this->belongsTo(Principal::class, 'principal_id');
    }

    public function principals()
    {
        return $this->belongsToMany(Principal::class);
    }
}
