<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Model;

class GithubSetting extends Model
{
    use BelongsToProfile;

    protected $fillable = [
        'profile_id',
        'username',
        'access_token',
        'auto_sync',
        'last_synced_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'auto_sync' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
    ];
}
