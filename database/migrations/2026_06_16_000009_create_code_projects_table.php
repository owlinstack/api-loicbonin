<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create code_projects table
        Schema::create('code_projects', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Add code_project_id to code_folders
        Schema::table('code_folders', function (Blueprint $table): void {
            $table->foreignUlid('code_project_id')->nullable()
                ->constrained('code_projects')->nullOnDelete();
        });

        // 3. Add code_file_id, code_folder_id, code_project_id to articles
        Schema::table('articles', function (Blueprint $table): void {
            $table->foreignUlid('code_file_id')->nullable()
                ->constrained('code_files')->nullOnDelete();
            $table->foreignUlid('code_folder_id')->nullable()
                ->constrained('code_folders')->nullOnDelete();
            $table->foreignUlid('code_project_id')->nullable()
                ->constrained('code_projects')->nullOnDelete();
        });

        // 4. Drop linked_article_id from code_files
        Schema::table('code_files', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('linked_article_id');
        });
    }

    public function down(): void
    {
        Schema::table('code_files', function (Blueprint $table): void {
            $table->foreignUlid('linked_article_id')->nullable()
                ->constrained('articles')->nullOnDelete();
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('code_file_id');
            $table->dropConstrainedForeignId('code_folder_id');
            $table->dropConstrainedForeignId('code_project_id');
        });

        Schema::table('code_folders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('code_project_id');
        });

        Schema::dropIfExists('code_projects');
    }
};
