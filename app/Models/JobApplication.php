<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 7.3 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Job application tracking record for Kanban board workflow.
 */
class JobApplication extends Model
{
    use BelongsToProfile;
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'company',
        'role',
        'job_url',
        'salary_range',
        'status',
        'applied_at',
        'notes',
        'resume_generation_id',
        'cover_letter_generation_id',
    ];

    protected $casts = [
        'applied_at' => 'date',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function resumeGeneration()
    {
        return $this->belongsTo(ResumeGeneration::class);
    }

    public function coverLetterGeneration()
    {
        return $this->belongsTo(CoverLetterGeneration::class);
    }
}
