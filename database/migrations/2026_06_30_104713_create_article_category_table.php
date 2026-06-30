<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_category', function (Blueprint $table): void {
            $table->foreignUlid('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['article_id', 'category_id']);
        });

        // Migrate existing category_id data to the pivot table
        $articles = DB::table('articles')->whereNotNull('category_id')->get();
        foreach ($articles as $article) {
            DB::table('article_category')->insert([
                'article_id' => $article->id,
                'category_id' => $article->category_id,
            ]);
        }

        // Drop category_id column and its foreign key constraint from articles table
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->foreignUlid('category_id')->nullable()->constrained('categories')->nullOnDelete();
        });

        // Migrate pivot table data back to articles table (taking the first category associated)
        $relations = DB::table('article_category')->get();
        foreach ($relations as $relation) {
            DB::table('articles')
                ->where('id', $relation->article_id)
                ->whereNull('category_id')
                ->update(['category_id' => $relation->category_id]);
        }

        Schema::dropIfExists('article_category');
    }
};
