<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_projects(): void
    {
        Project::create([
            'title' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'Short description',
            'long_description' => 'Longer description',
            'tech_stack' => ['React', 'TypeScript'],
            'live_url' => 'https://alpha.example.com',
            'repo_url' => 'https://github.com/example/alpha',
            'featured' => true,
            'year' => '2026',
        ]);

        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'slug',
                    'title',
                    'description',
                    'longDescription',
                    'techStack',
                    'liveUrl',
                    'repoUrl',
                    'featured',
                    'year',
                ],
            ]);

        $response->assertJsonFragment(['slug' => 'project-alpha', 'year' => '2026']);
    }

    public function test_can_get_single_project(): void
    {
        Project::create([
            'title' => 'Project Beta',
            'slug' => 'project-beta',
            'description' => 'Short description',
            'long_description' => 'Longer description',
            'tech_stack' => ['Laravel'],
            'featured' => false,
            'year' => '2025',
        ]);

        $response = $this->getJson('/api/v1/projects/project-beta');

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Project Beta')
            ->assertJsonPath('slug', 'project-beta')
            ->assertJsonPath('techStack.0', 'Laravel')
            ->assertJsonPath('year', '2025');
    }

    public function test_get_non_existent_project_returns_404(): void
    {
        $response = $this->getJson('/api/v1/projects/non-existent');
        $response->assertStatus(404);
    }

    public function test_projects_are_listed_in_sort_order_ascending(): void
    {
        Project::create([
            'title' => 'Third Project',
            'slug' => 'third-project',
            'description' => 'Desc',
            'tech_stack' => ['HTML'],
            'sort_order' => 3,
        ]);

        Project::create([
            'title' => 'First Project',
            'slug' => 'first-project',
            'description' => 'Desc',
            'tech_stack' => ['HTML'],
            'sort_order' => 1,
        ]);

        Project::create([
            'title' => 'Second Project',
            'slug' => 'second-project',
            'description' => 'Desc',
            'tech_stack' => ['HTML'],
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(200);
        $this->assertEquals('first-project', $response->json('0.slug'));
        $this->assertEquals('second-project', $response->json('1.slug'));
        $this->assertEquals('third-project', $response->json('2.slug'));
    }
}
