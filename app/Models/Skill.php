<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use BelongsToProfile;

    protected $fillable = [
        'profile_id',
        'name',
        'category',
        'proficiency',
        'icon',
        'sort_order',
    ];
}
