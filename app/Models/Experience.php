<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use BelongsToProfile;

    protected $fillable = [
        'profile_id',
        'title',
        'company',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];
}
