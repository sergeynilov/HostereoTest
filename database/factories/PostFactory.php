<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'created_at' => fake()->dateTimeBetween('-3 weeks', '-1 week'),
            'deleted_at' => fake()->boolean(chanceOfGettingTrue: 20) ? fake()->dateTimeBetween('-1 week',
                '-1 minute') : null,
        ];
    }
}
