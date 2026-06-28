<?php

declare(strict_types=1);

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
        Schema::table('code_files', function (Blueprint $table): void {
            $table->foreignUlid('folder_id')->nullable()->change();
        });

        Schema::table('profiles', function (Blueprint $table): void {
            $table->boolean('show_timeline')->nullable()->default(true)->change();
            $table->boolean('show_education')->nullable()->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('code_files', function (Blueprint $table): void {
            $table->foreignUlid('folder_id')->change();
        });

        Schema::table('profiles', function (Blueprint $table): void {
            $table->boolean('show_timeline')->default(true)->change();
            $table->boolean('show_education')->default(true)->change();
        });
    }
};
