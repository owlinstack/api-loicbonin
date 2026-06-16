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
                ]
            ]);

        $response->assertJsonFragment(['slug' => 'project-alpha']);
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
        ]);

        $response = $this->getJson('/api/v1/projects/project-beta');

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Project Beta')
            ->assertJsonPath('slug', 'project-beta')
            ->assertJsonPath('techStack.0', 'Laravel');
    }

    public function test_get_non_existent_project_returns_404(): void
    {
        $response = $this->getJson('/api/v1/projects/non-existent');
        $response->assertStatus(404);
    }
}
