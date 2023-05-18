<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class languagesWithInitData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('languages')->insert([
            'id'     => 1,
            'locale' => 'Українська мова',
            'prefix' => 'UA'
        ]);

        \DB::table('languages')->insert([
            'id'     => 2,
            'locale' => 'Русский язык',
            'prefix' => 'RU'
        ]);

        \DB::table('languages')->insert([
            'id'     => 3,
            'locale' => 'English language',
            'prefix' => 'EN'
        ]);

    }
}
