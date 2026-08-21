<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'profile_id' => Profile::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->randomNumber(5),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'tech_stack' => [fake()->word(), fake()->word()],
            'is_featured' => false,
            'sort_order' => 1,
        ];
    }
}
