<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Window extends Model
{
    protected $fillable = ['name'];

    public function principals(): BelongsToMany
    {
        // This links back to your Departments/Principals table
        return $this->belongsToMany(Principal::class, 'department_window', 'window_id', 'department_id');
    }
}
