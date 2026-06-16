<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_files', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('path')->unique();
            $table->string('language');
            $table->longText('content');
            $table->foreignUlid('folder_id')->constrained('code_folders')->cascadeOnDelete();
            $table->foreignUlid('linked_article_id')->nullable()
                  ->constrained('articles')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_files');
    }
};
