<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_gallery_images', function (Blueprint $table): void {
            $table->string('category')->nullable()->after('website_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('website_gallery_images', function (Blueprint $table): void {
            $table->dropColumn('category');
        });
    }
};
