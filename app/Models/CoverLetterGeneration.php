<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 7.2 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * AI-generated tailored cover letter for a specific job application.
 */
class CoverLetterGeneration extends Model
{
    use BelongsToProfile;
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'job_title',
        'company_name',
        'job_description',
        'content',
        'status',
        'error_message',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
