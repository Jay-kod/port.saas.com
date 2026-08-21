<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Models\Profile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Phase 7.2 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Generates tailored cover letters for a candidate profile and target job description.
 */
class CoverLetterService
{
    public function __construct(protected ?AiSetting $aiSetting = null)
    {
        $this->aiSetting ??= AiSetting::query()->where('is_active', true)->first();
    }

    public function generate(Profile $profile, string $jobTitle, string $companyName, string $jobDescription): string
    {
        if (! $this->aiSetting || ! $this->aiSetting->api_key) {
            return $this->fallbackGenerate($profile, $jobTitle, $companyName);
        }

        $prompt = $this->buildPrompt($profile, $jobTitle, $companyName, $jobDescription);

        try {
            return match ($this->aiSetting->provider) {
                'anthropic' => $this->callAnthropic($prompt),
                default => $this->callOpenAi($prompt),
            };
        } catch (\Throwable $e) {
            return $this->fallbackGenerate($profile, $jobTitle, $companyName);
        }
    }

    protected function buildPrompt(Profile $profile, string $jobTitle, string $companyName, string $jobDescription): string
    {
        $skills = $profile->skills->pluck('name')->implode(', ');
        $recentExp = $profile->experiences->take(2)->map(fn ($e) => "{$e->role} at {$e->company}: {$e->description}")->implode("\n");

        return <<<PROMPT
You are an expert executive cover letter writer. Write a persuasive, professionally tailored, and compelling cover letter for the following candidate applying to {$companyName} for the {$jobTitle} position.

Candidate Information:
Name: {$profile->full_name}
Headline: {$profile->headline}
Bio: {$profile->bio}
Skills: {$skills}
Recent Experience:
{$recentExp}

Target Role:
Title: {$jobTitle}
Company: {$companyName}
Job Description:
{$jobDescription}

Instructions:
- Write in first person ("I am excited to apply...").
- Hook the reader in the opening paragraph with enthusiasm for {$companyName}.
- In the body paragraphs, connect candidate skills and concrete achievements to key requirements in the job description.
- Keep tone professional, confident, and engaging.
- Provide only the clean cover letter text (including standard greeting and sign-off).
PROMPT;
    }

    protected function callOpenAi(string $prompt): string
    {
        $response = Http::withToken($this->aiSetting->api_key)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->aiSetting->model ?: 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You write outstanding tailored cover letters.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        return trim((string) $response->json('choices.0.message.content'));
    }

    protected function callAnthropic(string $prompt): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->aiSetting->api_key,
            'anthropic-version' => '2023-06-01',
        ])
            ->timeout(30)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->aiSetting->model ?: 'claude-3-5-sonnet-latest',
                'max_tokens' => 2048,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

        return trim((string) $response->json('content.0.text'));
    }

    public function fallbackGenerate(Profile $profile, string $jobTitle, string $companyName): string
    {
        $skills = $profile->skills->take(4)->pluck('name')->implode(', ') ?: 'modern software development and cloud architecture';

        return <<<LETTER
Dear Hiring Team at {$companyName},

I am writing to express my strong interest in the {$jobTitle} role at {$companyName}. With my background as a {$profile->headline} and hands-on expertise in {$skills}, I am excited about the opportunity to contribute to your team's mission.

Throughout my career, I have focused on delivering scalable, high-impact engineering solutions. My experience aligns closely with the goals of {$companyName}, where I can leverage my problem-solving skills and technical background to build resilient systems and drive product innovation.

Thank you for considering my application. I look forward to the possibility of discussing how my experience and passion can add immediate value to {$companyName}.

Sincerely,
{$profile->full_name}
LETTER;
    }
}
