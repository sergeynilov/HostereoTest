<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class post_tagsWithInitData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $tags  = Tag::get();
        $posts = Post::get();
        foreach ($posts as $post) {
            PostTag::factory()->count(1)->create([
                'post_id' => $post->id,
                'tag_id'  => $faker->randomElement($tags)['id'],
            ]);
        }
    }
}
