<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeDepartment extends Model
{
    protected $fillable = ['company_id', 'name', 'description', 'is_active'];

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function principals() {
        return $this->hasMany(Principal::class, 'department_id');
    }
}
