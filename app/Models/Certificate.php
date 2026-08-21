<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use BelongsToProfile;

    protected $fillable = [
        'profile_id',
        'title',
        'slug',
        'issuer',
        'issue_date',
        'credential_url',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
