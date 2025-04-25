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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->string('subheading')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('meta_title')->nullable(); // Add 'meta_title' field
            $table->text('meta_description')->nullable(); // Add 'meta_description' field
            $table->date('published_on')->nullable();
            $table->longText('content');
            $table->boolean('pinned')->default(false);
            $table->string('slug')->nullable()->unique(); // or any appropriate column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
