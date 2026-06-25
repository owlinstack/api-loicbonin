<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour ProjectService.
 * Justification : Valide la logique de tri et de récupération des projets isolée du contrôleur.
 */
final class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectService $projectService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectService = new ProjectService();
    }

    /**
     * Teste que la méthode listAll retourne les projets correctement triés.
     */
    public function test_list_all_returns_projects_sorted_by_sort_order_then_created_at(): void
    {
        $fixedDate = '2026-06-25 12:00:00';

        // Projet 3 : sort_order = 2 (le plus récent)
        Project::create([
            'title' => 'Project C',
            'slug' => 'project-c',
            'description' => 'Desc',
            'tech_stack' => ['HTML'],
            'sort_order' => 2,
            'created_at' => '2026-06-25 13:00:00',
        ]);

        // Projet 1 : sort_order = 1
        Project::create([
            'title' => 'Project A',
            'slug' => 'project-a',
            'description' => 'Desc',
            'tech_stack' => ['HTML'],
            'sort_order' => 1,
            'created_at' => $fixedDate,
        ]);

        // Projet 2 : sort_order = 2 (plus ancien)
        Project::create([
            'title' => 'Project B',
            'slug' => 'project-b',
            'description' => 'Desc',
            'tech_stack' => ['HTML'],
            'sort_order' => 2,
            'created_at' => '2026-06-25 11:00:00',
        ]);

        $results = $this->projectService->listAll();

        $this->assertCount(3, $results);
        $slugs = $results->pluck('slug')->all();

        // Doit être : project-a (sort_order=1), puis project-c (sort_order=2, plus récent), puis project-b (sort_order=2, plus ancien)
        $this->assertEquals(['project-a', 'project-c', 'project-b'], $slugs);
    }

    /**
     * Teste findBySlug pour récupérer un projet ou null.
     */
    public function test_find_by_slug_returns_project_or_null(): void
    {
        Project::create([
            'title' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'Desc',
            'tech_stack' => ['HTML'],
        ]);

        $project = $this->projectService->findBySlug('project-alpha');
        $this->assertNotNull($project);
        $this->assertEquals('Project Alpha', $project->title);

        $nonExistent = $this->projectService->findBySlug('non-existent');
        $this->assertNull($nonExistent);
    }
}
