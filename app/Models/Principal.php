<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Principal extends Model
{
    protected $fillable = ['department_id', 'name', 'contact_info'];

    public function department() {
        return $this->belongsTo(OfficeDepartment::class, 'department_id');
    }
}
