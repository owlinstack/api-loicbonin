<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Snapshots;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

/**
 * Test de contrat pour l'API des projets.
 * Justification : Verrouille la structure JSON exacte renvoyée au front-end Next.js pour éviter toute régression.
 */
final class ProjectSnapshotTest extends TestCase
{
    use MatchesSnapshots;
    use RefreshDatabase;

    /**
     * Teste le snapshot de la liste des projets.
     */
    public function test_projects_list_matches_snapshot(): void
    {
        $fixedDate = '2026-06-25 12:00:00';

        /** @var Project $project */
        Project::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y4',
            'title' => 'Mon Premier Projet',
            'slug' => 'mon-premier-projet',
            'description' => 'Petite description.',
            'long_description' => 'Description détaillée sur plusieurs paragraphes.',
            'tech_stack' => ['Laravel', 'Vue', 'Tailwind'],
            'live_url' => 'https://example.com',
            'repo_url' => 'https://github.com/example/repo',
            'featured' => true,
            'year' => 2026,
            'sort_order' => 1,
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(200);

        $this->assertMatchesJsonSnapshotWithNormalizedIds($response->getContent());
    }

    /**
     * Teste le snapshot d'un projet individuel.
     */
    public function test_project_show_matches_snapshot(): void
    {
        $fixedDate = '2026-06-25 12:00:00';

        /** @var Project $project */
        Project::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y5',
            'title' => 'Projet Individuel',
            'slug' => 'projet-individuel',
            'description' => 'Description.',
            'long_description' => 'Description longue.',
            'tech_stack' => ['NextJS', 'TypeScript'],
            'live_url' => null,
            'repo_url' => null,
            'featured' => false,
            'year' => 2025,
            'sort_order' => 2,
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        $response = $this->getJson('/api/v1/projects/projet-individuel');

        $response->assertStatus(200);

        $this->assertMatchesJsonSnapshotWithNormalizedIds($response->getContent());
    }

    /**
     * Normalise les ULIDs dynamiques pour garantir des snapshots déterministes.
     */
    private function assertMatchesJsonSnapshotWithNormalizedIds(string $json): void
    {
        $normalizedJson = preg_replace('/[0-7][0-9a-hjkmnp-tv-z]{25}/i', '00000000000000000000000000', $json);
        $this->assertMatchesJsonSnapshot($normalizedJson);
    }
}
