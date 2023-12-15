<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apps extends Model
{
    use HasFactory;

    protected $fillable = [
        'icon',
        'URL',
    ];

    //many to many r
    public function roles(): BelongsToMany{
        return $this->belongsToMany(roles::class, 'role_apps', 'apps_id', 'role_id');
    }
}
