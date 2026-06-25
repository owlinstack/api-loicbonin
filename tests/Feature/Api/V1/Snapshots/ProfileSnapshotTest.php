<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Snapshots;

use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

/**
 * Test de contrat pour l'API du profil.
 * Justification : Verrouille la structure JSON exacte renvoyée au front-end Next.js pour éviter toute régression.
 */
final class ProfileSnapshotTest extends TestCase
{
    use MatchesSnapshots;
    use RefreshDatabase;

    /**
     * Teste le snapshot des données du profil avec le fallback par défaut (quand aucun profil n'est en BDD).
     */
    public function test_profile_fallback_matches_snapshot(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(200);

        $this->assertMatchesJsonSnapshotWithNormalizedIds($response->getContent());
    }

    /**
     * Teste le snapshot des données du profil chargées depuis la base de données.
     */
    public function test_profile_from_db_matches_snapshot(): void
    {
        $fixedDate = '2026-06-25 12:00:00';

        /** @var Profile $profile */
        Profile::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y6',
            'name' => 'Loïc Bonin de BDD',
            'bio' => 'Ma super bio personnalisée en base de données.',
            'skills' => ['PHP', 'Laravel', 'Docker', 'NextJS'],
            'show_timeline' => true,
            'timeline' => [
                ['year' => '2026', 'title' => 'Dev senior', 'company' => 'MyCompany', 'description' => 'Super job.'],
            ],
            'show_education' => true,
            'education' => [
                ['year' => '2020', 'degree' => 'Master', 'school' => 'MySchool'],
            ],
            'cv_path' => 'cvs/mon-cv.pdf',
            'avatar_path' => 'avatars/avatar.jpg',
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        $response = $this->getJson('/api/v1/profile');

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
