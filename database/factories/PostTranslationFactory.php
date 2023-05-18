<?php

namespace Database\Factories;

use App\Models\PostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PostTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
        ];
    }


    public function languagePrefix($prefix)
    {
        return $this->state(function (array $attributes) use ($prefix) {
            return [
                'title'       => $prefix . ' : ' . $this->faker->text(50),
                'description' => $prefix . ' : ' . $this->faker->paragraphs(rand(2, 5), true),
                'content'     => $prefix . ' : ' . $this->faker->paragraphs(rand(5, 10), true)
            ];
        });
    }


}
