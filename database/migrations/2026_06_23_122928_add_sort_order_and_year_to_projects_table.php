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
        Schema::table('projects', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('featured');
            $table->string('year')->nullable()->after('sort_order');
        });

        // Initialize sort_order sequentially for existing projects based on their creation date
        $projects = DB::table('projects')->orderBy('created_at', 'asc')->get();
        foreach ($projects as $index => $project) {
            DB::table('projects')->where('id', $project->id)->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['sort_order', 'year']);
        });
    }
};
