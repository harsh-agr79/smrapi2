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
        Schema::table('meta_tags', function (Blueprint $table) {
            $table->string('meta_image')->nullable()->after('meta_description');
            $table->string('meta_image_alt')->nullable()->after('meta_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meta_tags', function (Blueprint $table) {
            //
        });
    }
};
