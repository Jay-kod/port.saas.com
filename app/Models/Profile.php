<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * "A" portfolio's data (Phase 1 turned this from a singleton into one
 * row per tenant — see docs/agents/02-MULTI-TENANCY-FOUNDATION.md).
 *
 * Always resolve "the current profile" through
 * App\Services\CurrentProfileResolver rather than calling
 * Profile::first() directly in new code.
 */
class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'slug',
        'full_name',
        'headline',
        'bio',
        'email',
        'phone',
        'location',
        'avatar_path',
        'resume_path',
        'social_links',
        'is_published',
        'is_discoverable',
        'meta_description',
        'theme_id',
        'theme_mode_default',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_published' => 'boolean',
        'is_discoverable' => 'boolean',
    ];

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function resumeGenerations()
    {
        return $this->hasMany(ResumeGeneration::class);
    }

    public function coverLetterGenerations()
    {
        return $this->hasMany(CoverLetterGeneration::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function githubSetting()
    {
        return $this->hasOne(GithubSetting::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function customDomain()
    {
        return $this->hasOne(Domain::class)->whereNotNull('verified_at');
    }

    public function portfolioReports()
    {
        return $this->hasMany(PortfolioReport::class);
    }
}
