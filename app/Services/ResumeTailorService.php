<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Models\Profile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Tailors a Profile's experience/skills into a job-specific resume
 * payload using whichever AI provider is configured in AiSetting.
 *
 * SaaS NOTE (Phase 4): this pipeline is reused as-is by the SaaS
 * transformation plan. The only change is that callers (namely
 * CreateResume in the Filament admin) wrap calls to generate() with a
 * per-Account usage-quota check before invoking it, and a BYOK
 * (bring-your-own-key) exemption. See
 * docs/agents/03-BILLING-ONBOARDING-ROUTING.md, section 4.3. Nothing
 * in this class itself needs to change for that.
 */
class ResumeTailorService
{
    public function __construct(protected ?AiSetting $aiSetting = null)
    {
        $this->aiSetting ??= AiSetting::query()->where('is_active', true)->first();
    }

    /**
     * @return array{summary: string, highlighted_skills: array, tailored_bullets: array}
     */
    public function generate(Profile $profile, string $jobTitle, string $jobDescription): array
    {
        if (! $this->aiSetting || ! $this->aiSetting->api_key) {
            throw new RuntimeException(
                'No active AI provider is configured. Add an API key under AI Settings before generating a tailored resume.'
            );
        }

        $prompt = $this->buildPrompt($profile, $jobTitle, $jobDescription);

        return match ($this->aiSetting->provider) {
            'anthropic' => $this->callAnthropic($prompt),
            default => $this->callOpenAi($prompt),
        };
    }

    protected function buildPrompt(Profile $profile, string $jobTitle, string $jobDescription): string
    {
        return sprintf(
            "You are a professional resume writer. Given the candidate's background below and a target job, ".
            "return a JSON object with keys: summary (string), highlighted_skills (array of strings), ".
            "tailored_bullets (array of strings).\n\nCandidate: %s\nHeadline: %s\nBio: %s\n\n".
            "Target job title: %s\nJob description: %s",
            $profile->full_name,
            $profile->headline,
            $profile->bio,
            $jobTitle,
            $jobDescription,
        );
    }

    protected function callOpenAi(string $prompt): array
    {
        $response = Http::withToken($this->aiSetting->api_key)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->aiSetting->model ?: 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'response_format' => ['type' => 'json_object'],
            ]);

        return $this->parseJsonResponse($response->json('choices.0.message.content'));
    }

    protected function callAnthropic(string $prompt): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->aiSetting->api_key,
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->aiSetting->model ?: 'claude-3-5-sonnet-latest',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]);

        return $this->parseJsonResponse($response->json('content.0.text'));
    }

    protected function parseJsonResponse(?string $raw): array
    {
        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('AI provider returned an unparsable response.');
        }

        return [
            'summary' => $decoded['summary'] ?? '',
            'highlighted_skills' => $decoded['highlighted_skills'] ?? [],
            'tailored_bullets' => $decoded['tailored_bullets'] ?? [],
        ];
    }
}
