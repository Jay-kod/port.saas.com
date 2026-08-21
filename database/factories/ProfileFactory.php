<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'slug' => Str::slug($name) . '-' . fake()->unique()->randomNumber(5),
            'full_name' => $name,
            'headline' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'email' => fake()->unique()->safeEmail(),
            'location' => fake()->city(),
            'is_published' => true,
        ];
    }
}
