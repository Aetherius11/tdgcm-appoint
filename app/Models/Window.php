<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Window extends Model
{
    protected $fillable = ['name'];

    public function principals(): BelongsToMany
    {
        return $this->belongsToMany(Principal::class, 'principal_window', 'window_id', 'principal_id');
    }
}
