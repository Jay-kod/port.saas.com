<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\Certificate;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Services\ResumeParserService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;

/**
 * Phase 7.1 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * AI-powered resume import and interactive review page.
 */
class ResumeImport extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Import Resume';

    protected static ?string $title = 'AI Resume Import & Parser';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.resume-import';

    public int $step = 1; // 1 = Upload, 2 = Review & Confirm

    public $resumeFile = null;

    public string $resumeText = '';

    public array $parsedData = [
        'full_name' => '',
        'headline' => '',
        'bio' => '',
        'email' => '',
        'phone' => '',
        'location' => '',
        'experiences' => [],
        'skills' => [],
        'projects' => [],
        'certificates' => [],
    ];

    public function getAccount(): ?Account
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account;
    }

    public function getProfile(): ?Profile
    {
        $account = $this->getAccount();

        return $account?->profiles()->first() ?? Profile::query()->first();
    }

    public function parseResume(): void
    {
        $parser = app(ResumeParserService::class);
        $rawText = '';

        if ($this->resumeFile) {
            try {
                $path = $this->resumeFile->getRealPath();
                $rawText = $parser->extractTextFromPdf($path);
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('PDF Extraction Failed')
                    ->body('Could not read text from uploaded PDF. Try pasting the resume text directly.')
                    ->danger()
                    ->send();

                return;
            }
        } elseif (! empty(trim($this->resumeText))) {
            $rawText = $this->resumeText;
        } else {
            Notification::make()
                ->title('No Resume Provided')
                ->body('Please upload a PDF resume file or paste resume text.')
                ->warning()
                ->send();

            return;
        }

        try {
            $this->parsedData = $parser->parse($rawText);
            $this->step = 2;

            Notification::make()
                ->title('Resume Parsed with AI')
                ->body('Review and edit the extracted information below before saving to your portfolio.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Parsing Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function importParsedData(): void
    {
        $profile = $this->getProfile();

        if (! $profile) {
            Notification::make()
                ->title('No Profile Found')
                ->danger()
                ->send();

            return;
        }

        // 1. Update Profile Information
        $profile->update([
            'full_name' => $this->parsedData['full_name'] ?: $profile->full_name,
            'headline' => $this->parsedData['headline'] ?: $profile->headline,
            'bio' => $this->parsedData['bio'] ?: $profile->bio,
            'email' => $this->parsedData['email'] ?: $profile->email,
            'phone' => $this->parsedData['phone'] ?: $profile->phone,
            'location' => $this->parsedData['location'] ?: $profile->location,
        ]);

        // 2. Import Experiences
        if (! empty($this->parsedData['experiences'])) {
            foreach ($this->parsedData['experiences'] as $index => $exp) {
                $role = $exp['role'] ?? ($exp['title'] ?? 'Developer');
                $company = $exp['company'] ?? 'Company';
                if (! empty($company)) {
                    Experience::create([
                        'profile_id' => $profile->id,
                        'title' => $role,
                        'company' => $company,
                        'start_date' => $exp['start_date'] ?? now()->subYears(2)->format('Y-m-d'),
                        'end_date' => $exp['end_date'] ?? null,
                        'is_current' => ! empty($exp['is_current']),
                        'description' => $exp['description'] ?? '',
                        'location' => $exp['location'] ?? null,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        }

        // 3. Import Skills
        if (! empty($this->parsedData['skills'])) {
            foreach ($this->parsedData['skills'] as $index => $skill) {
                if (! empty($skill['name'])) {
                    Skill::firstOrCreate([
                        'profile_id' => $profile->id,
                        'name' => $skill['name'],
                    ], [
                        'category' => $skill['category'] ?? 'General',
                        'proficiency' => $skill['proficiency'] ?? 80,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        }

        // 4. Import Projects
        if (! empty($this->parsedData['projects'])) {
            foreach ($this->parsedData['projects'] as $index => $project) {
                if (! empty($project['title'])) {
                    $slug = \Illuminate\Support\Str::slug($project['title']) . '-' . rand(100, 999);
                    Project::create([
                        'profile_id' => $profile->id,
                        'title' => $project['title'],
                        'slug' => $slug,
                        'description' => $project['description'] ?? '',
                        'tech_stack' => $project['tech_stack'] ?? [],
                        'sort_order' => $index + 1,
                        'is_featured' => $index < 3,
                    ]);
                }
            }
        }

        Notification::make()
            ->title('Portfolio Updated Successfully')
            ->body('Your resume data has been imported into your developer portfolio.')
            ->success()
            ->send();

        $this->step = 1;
        $this->resumeFile = null;
        $this->resumeText = '';
    }

    public function resetForm(): void
    {
        $this->step = 1;
        $this->resumeFile = null;
        $this->resumeText = '';
    }
}
