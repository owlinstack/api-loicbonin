<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour CategoryService.
 * Justification : Valide la logique d'agrégation et de comptage des articles par catégorie.
 */
final class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService;
    }

    /**
     * Teste que la méthode listWithPublishedArticlesCount retourne les catégories avec le bon décompte d'articles publiés.
     */
    public function test_list_with_published_articles_count_only_counts_published(): void
    {
        /** @var Category $category */
        $category = Category::create([
            'label' => 'PHP',
            'slug' => 'php',
        ]);

        // Article publié
        $artPub = Article::create([
            'title' => 'Article publié',
            'slug' => 'article-publie',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
        ]);
        $artPub->categories()->attach($category->id);

        // Article brouillon (ne doit pas être compté)
        $artDraft = Article::create([
            'title' => 'Article brouillon',
            'slug' => 'article-brouillon',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Draft,
            'reading_time' => 3,
        ]);
        $artDraft->categories()->attach($category->id);

        $results = $this->categoryService->listWithPublishedArticlesCount();

        $this->assertCount(1, $results);
        $first = $results->first();
        $this->assertNotNull($first);
        $this->assertEquals('PHP', $first->label);
        // withCount ajoute un attribut articles_count
        $this->assertEquals(1, $first->articles_count);
    }
}
