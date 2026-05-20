<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'branding_logo', 'privacy_policy', 'is_active', 'landing_headline', 'landing_badge_image', 'landing_body'];

    public function departments() {
        return $this->hasMany(OfficeDepartment::class);
    }
}
