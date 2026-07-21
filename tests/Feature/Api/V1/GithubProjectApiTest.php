<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\GithubProjectStatus;
use App\Models\GithubProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GithubProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_published_github_projects(): void
    {
        GithubProject::create([
            'name' => 'Awesome Package',
            'slug' => 'awesome-package',
            'description' => 'A great open source package',
            'github_url' => 'https://github.com/example/awesome-package',
            'status' => GithubProjectStatus::Published,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/v1/github-projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'githubUrl',
                    'status',
                    'sortOrder',
                ],
            ]);

        $response->assertJsonFragment([
            'slug' => 'awesome-package',
            'githubUrl' => 'https://github.com/example/awesome-package',
            'status' => 'published',
        ]);
    }

    public function test_draft_or_archived_github_projects_are_excluded(): void
    {
        GithubProject::create([
            'name' => 'Published Project',
            'slug' => 'published-project',
            'github_url' => 'https://github.com/example/published',
            'status' => GithubProjectStatus::Published,
        ]);

        GithubProject::create([
            'name' => 'Draft Project',
            'slug' => 'draft-project',
            'github_url' => 'https://github.com/example/draft',
            'status' => GithubProjectStatus::Draft,
        ]);

        GithubProject::create([
            'name' => 'Archived Project',
            'slug' => 'archived-project',
            'github_url' => 'https://github.com/example/archived',
            'status' => GithubProjectStatus::Archived,
        ]);

        $response = $this->getJson('/api/v1/github-projects');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals('published-project', $data[0]['slug']);
    }

    public function test_github_projects_are_sorted_by_sort_order(): void
    {
        GithubProject::create([
            'name' => 'Third Project',
            'slug' => 'third',
            'github_url' => 'https://github.com/example/third',
            'status' => GithubProjectStatus::Published,
            'sort_order' => 3,
        ]);

        GithubProject::create([
            'name' => 'First Project',
            'slug' => 'first',
            'github_url' => 'https://github.com/example/first',
            'status' => GithubProjectStatus::Published,
            'sort_order' => 1,
        ]);

        GithubProject::create([
            'name' => 'Second Project',
            'slug' => 'second',
            'github_url' => 'https://github.com/example/second',
            'status' => GithubProjectStatus::Published,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/v1/github-projects');

        $response->assertStatus(200);
        $this->assertEquals('first', $response->json('0.slug'));
        $this->assertEquals('second', $response->json('1.slug'));
        $this->assertEquals('third', $response->json('2.slug'));
    }
}
