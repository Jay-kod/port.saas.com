<?php

namespace App\Services;

use App\Models\Experience;
use App\Models\Profile;
use App\Models\ResumeGeneration;
use App\Models\Skill;
use App\Models\Template;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/**
 * Renders a Profile + a ResumeGeneration's tailored content through a
 * Template's Blade view and converts it to a downloadable PDF.
 *
 * SaaS NOTE: reused as-is by the SaaS transformation plan. The global
 * template catalog and rendering pipeline don't change; templates just
 * gain an optional account_id for private/custom templates in later
 * phases. See AGENTS.md (project root), "what this plan deliberately
 * does not change".
 *
 * Experience/Skill are queried directly (unscoped) here because
 * profile_id doesn't exist yet (pre-Phase-1). Once Phase 1 adds it,
 * scope these through $profile->experiences()/$profile->skills().
 */
class TemplateConversionService
{
    public function renderHtml(Profile $profile, Template $template, ?ResumeGeneration $generation = null): string
    {
        return view($template->blade_view, [
            'profile' => $profile,
            'experiences' => Experience::query()->orderByDesc('start_date')->get(),
            'skills' => Skill::query()->orderBy('sort_order')->get(),
            'generation' => $generation,
        ])->render();
    }

    public function toPdf(Profile $profile, Template $template, ?ResumeGeneration $generation = null): string
    {
        $html = $this->renderHtml($profile, $template, $generation);

        $fileName = 'resumes/'.Str::slug($profile->full_name).'-'.Str::random(8).'.pdf';

        $pdf = Pdf::loadHTML($html);

        $absolutePath = storage_path('app/public/'.$fileName);
        @mkdir(dirname($absolutePath), recursive: true);
        $pdf->save($absolutePath);

        return $fileName;
    }
}
