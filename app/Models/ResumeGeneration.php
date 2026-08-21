<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfile;
use Illuminate\Database\Eloquent\Model;

class ResumeGeneration extends Model
{
    use BelongsToProfile;

    protected $fillable = [
        'profile_id',
        'template_id',
        'job_title',
        'company_name',
        'job_description',
        'tailored_content',
        'pdf_path',
        'status',
        'error_message',
    ];

    protected $casts = [
        'tailored_content' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
