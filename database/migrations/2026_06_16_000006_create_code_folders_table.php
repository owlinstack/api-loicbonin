<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_folders', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('path')->unique();
            $table->foreignUlid('parent_id')->nullable()
                ->constrained('code_folders')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_folders');
    }
};
