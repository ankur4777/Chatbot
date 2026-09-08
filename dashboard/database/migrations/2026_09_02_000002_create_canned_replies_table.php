<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canned_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canned_replies');
    }
};
