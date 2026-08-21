<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use BelongsToProfile, HasFactory;

    protected $fillable = [
        'profile_id',
        'title',
        'slug',
        'summary',
        'description',
        'tech_stack',
        'repo_url',
        'live_url',
        'image_path',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'is_featured' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
