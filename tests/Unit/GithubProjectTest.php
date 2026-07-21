<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\GithubProjectStatus;
use App\Models\GithubProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GithubProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_github_project_with_ulid_and_status_enum(): void
    {
        $project = GithubProject::create([
            'name' => 'Portfolio Next',
            'slug' => 'portfolio-next',
            'description' => 'Un projet front-end sous Next.js',
            'github_url' => 'https://github.com/loicbonin/front-loicbonin',
            'status' => GithubProjectStatus::Published,
            'sort_order' => 1,
        ]);

        $this->assertNotEmpty($project->id);
        $this->assertEquals(26, strlen($project->id));
        $this->assertInstanceOf(GithubProjectStatus::class, $project->status);
        $this->assertEquals(GithubProjectStatus::Published, $project->status);
    }

    public function test_can_convert_github_project_to_dto(): void
    {
        $project = GithubProject::create([
            'name' => 'API Laravel',
            'slug' => 'api-laravel',
            'description' => 'Backend API en PHP 8.3',
            'github_url' => 'https://github.com/loicbonin/api-loicbonin',
            'status' => GithubProjectStatus::Published,
            'sort_order' => 2,
        ]);

        $dto = $project->toData();

        $this->assertEquals($project->id, $dto->id);
        $this->assertEquals('API Laravel', $dto->name);
        $this->assertEquals('https://github.com/loicbonin/api-loicbonin', $dto->githubUrl);
        $this->assertEquals(GithubProjectStatus::Published, $dto->status);
        $this->assertEquals(2, $dto->sortOrder);
    }
}
