<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Snapshots;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

/**
 * Test de contrat pour l'API des articles.
 * Justification : Verrouille la structure JSON exacte renvoyée au front-end Next.js pour éviter toute régression.
 */
final class ArticleSnapshotTest extends TestCase
{
    use MatchesSnapshots;
    use RefreshDatabase;

    /**
     * Teste le snapshot de la liste des articles.
     */
    public function test_articles_list_matches_snapshot(): void
    {
        // Fixe une date de référence déterministe
        $fixedDate = '2026-06-25 12:00:00';

        /** @var Category $category */
        $category = Category::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y0',
            'label' => 'Tech',
            'slug' => 'tech',
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        /** @var Tag $tag */
        $tag = Tag::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y1',
            'name' => 'Laravel',
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        /** @var Article $article */
        $article = Article::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y2',
            'category_id' => $category->id,
            'title' => 'Mon Super Article de Test',
            'slug' => 'mon-super-article-de-test',
            'excerpt' => 'Résumé de l\'article.',
            'content' => 'Contenu détaillé de l\'article.',
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'featured' => false,
            'published_at' => $fixedDate,
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        $article->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200);

        // Vérification de la correspondance du snapshot
        $this->assertMatchesJsonSnapshotWithNormalizedIds($response->getContent());
    }

    /**
     * Teste le snapshot des détails d'un article individuel.
     */
    public function test_article_show_matches_snapshot(): void
    {
        $fixedDate = '2026-06-25 12:00:00';

        /** @var Category $category */
        $category = Category::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y0',
            'label' => 'Design',
            'slug' => 'design',
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        /** @var Article $article */
        $article = Article::create([
            'id' => '01h7x8y9z01h7x8y9z01h7x8y3',
            'category_id' => $category->id,
            'title' => 'Article Individuel',
            'slug' => 'article-individuel',
            'excerpt' => 'Résumé.',
            'content' => 'Contenu.',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'featured' => false,
            'published_at' => $fixedDate,
            'created_at' => $fixedDate,
            'updated_at' => $fixedDate,
        ]);

        $response = $this->getJson('/api/v1/articles/article-individuel');

        $response->assertStatus(200);

        $this->assertMatchesJsonSnapshotWithNormalizedIds($response->getContent());
    }

    /**
     * Normalise les ULIDs dynamiques pour garantir des snapshots déterministes.
     */
    private function assertMatchesJsonSnapshotWithNormalizedIds(string $json): void
    {
        // Remplace les ULIDs (26 caractères alphanumériques d'alphabet ULID) par un ID fixe
        $normalizedJson = preg_replace('/[0-7][0-9a-hjkmnp-tv-z]{25}/i', '00000000000000000000000000', $json);
        $this->assertMatchesJsonSnapshot($normalizedJson);
    }
}
