<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->references('id')->on('posts')->onUpdate('RESTRICT')->onDelete('CASCADE');

            $table->tinyInteger('language_id')->unsigned();
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('CASCADE');

            $table->string('title', 255);
            $table->mediumText('description');
            $table->mediumText('content');

            $table->unique(['post_id', 'language_id'], 'language_post_post_id_language_id_index');
        });

        Artisan::call('db:seed', array('--class' => 'post_translationsWithInitData'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
};
