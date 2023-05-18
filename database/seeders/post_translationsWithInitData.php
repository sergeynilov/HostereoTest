<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class post_translationsWithInitData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = Language::get();
        $posts     = Post::get();
        foreach ($languages as $language) {
            foreach ($posts as $post) {
                PostTranslation::factory()->languagePrefix($language->prefix)->count(1)->create([
                    'language_id' => $language->id,
                    'post_id'     => $post->id,
                ]);
            }
        }
    }
}
