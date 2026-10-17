<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->string('image_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'website_id', 'is_active']);
            $table->index(['website_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_gallery_images');
    }
};
