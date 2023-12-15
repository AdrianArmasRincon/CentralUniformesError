<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
    use HasFactory;
    protected $table = 'roles';
    protected $fillable = ['name'];

    //Many to many r
    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class);
    }

    public function apps(): BelongsToMany{
        return $this->belongsToMany(apps::class);
    }
}
