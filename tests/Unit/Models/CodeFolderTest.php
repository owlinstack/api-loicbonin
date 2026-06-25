<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CodeFolder;
use App\Models\CodeProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CodeFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_project_slug_returns_null_when_no_project_or_parent(): void
    {
        /** @var CodeFolder $folder */
        $folder = CodeFolder::create([
            'name' => 'standalone',
            'path' => 'standalone',
            'sort_order' => 1,
        ]);

        $this->assertNull($folder->getProjectSlug());
    }

    public function test_get_project_slug_returns_direct_project_slug(): void
    {
        /** @var CodeProject $project */
        $project = CodeProject::create([
            'name' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'A test project',
            'is_published' => true,
        ]);

        /** @var CodeFolder $folder */
        $folder = CodeFolder::create([
            'name' => 'src',
            'path' => 'src',
            'code_project_id' => $project->id,
            'sort_order' => 1,
        ]);

        $this->assertEquals('project-alpha', $folder->getProjectSlug());
    }

    public function test_get_project_slug_resolves_recursively_from_parents(): void
    {
        /** @var CodeProject $project */
        $project = CodeProject::create([
            'name' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'A test project',
            'is_published' => true,
        ]);

        /** @var CodeFolder $parentFolder */
        $parentFolder = CodeFolder::create([
            'name' => 'src',
            'path' => 'src',
            'code_project_id' => $project->id,
            'sort_order' => 1,
        ]);

        /** @var CodeFolder $childFolder */
        $childFolder = CodeFolder::create([
            'name' => 'components',
            'path' => 'src/components',
            'parent_id' => $parentFolder->id,
            'sort_order' => 1,
        ]);

        /** @var CodeFolder $grandchildFolder */
        $grandchildFolder = CodeFolder::create([
            'name' => 'Button',
            'path' => 'src/components/Button',
            'parent_id' => $childFolder->id,
            'sort_order' => 1,
        ]);

        $this->assertEquals('project-alpha', $grandchildFolder->getProjectSlug());
    }

    public function test_get_project_slug_uses_static_cache(): void
    {
        /** @var CodeProject $project */
        $project = CodeProject::create([
            'name' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'A test project',
            'is_published' => true,
        ]);

        /** @var CodeFolder $folder */
        $folder = CodeFolder::create([
            'name' => 'src',
            'path' => 'src',
            'code_project_id' => $project->id,
            'sort_order' => 1,
        ]);

        // First call loads from DB relation
        $this->assertEquals('project-alpha', $folder->getProjectSlug());

        // Delete project to prove cache is used instead of re-querying
        CodeProject::query()->where('id', $project->id)->delete();

        // Second call should hit the cache and return the same slug
        $this->assertEquals('project-alpha', $folder->getProjectSlug());
    }
}
