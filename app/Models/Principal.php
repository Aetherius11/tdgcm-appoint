<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Principal extends Model
{
    protected $fillable = ['department_id', 'name', 'contact_info', 'parent_id'];

    public function department() {
        return $this->belongsTo(OfficeDepartment::class, 'department_id');
    }

    public function purposes()
    {
        return $this->belongsToMany(Purpose::class);
    }

    public function parent() {
    return $this->belongsTo(Principal::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(Principal::class, 'parent_id');
    }
}
