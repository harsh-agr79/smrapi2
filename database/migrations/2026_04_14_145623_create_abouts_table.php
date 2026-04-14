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
        Schema::create('abouts', function (Blueprint $table) {
            $table->id();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('hero_text_above_title')->nullable();
            $table->string('hero_title')->nullable();
            $table->string('hero_description')->nullable();
            $table->json('statistics')->nullable();
            $table->string('who_title')->nullable();
            $table->longText('who_description')->nullable();
            $table->text('mission_text')->nullable();
            $table->text('vision_text')->nullable();
            $table->text('team_quote')->nullable();
            $table->text('why_choose_title')->nullable();
            $table->json('why_choose_cards')->nullable();
            $table->text('cta_title')->nullable();
            $table->text('cta_description')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('cta_button_text2')->nullable();
            $table->string('cta_button_link')->nullable();
            $table->string('cta_button_link2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abouts');
    }
};
