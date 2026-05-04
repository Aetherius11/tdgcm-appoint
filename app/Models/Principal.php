<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Principal extends Model
{
    protected $fillable = ['department_id', 'name', 'contact_info', 'parent_id'];
    protected $table = 'principals';

    public function department(): BelongsTo {
        return $this->belongsTo(OfficeDepartment::class, 'department_id');
    }

    public function purposes(): BelongsToMany
    {
        // Explicitly define the pivot table if it isn't following Laravel naming conventions
        return $this->belongsToMany(Purpose::class, 'principal_purpose');
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(Principal::class, 'parent_id');
    }

    public function children(): HasMany {
        return $this->hasMany(Principal::class, 'parent_id');
    }
}
