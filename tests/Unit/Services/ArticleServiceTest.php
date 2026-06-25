<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Services\ArticleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ArticleService $articleService;

    private Category $catBackend;

    private Category $catFrontend;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articleService = new ArticleService;

        $this->catBackend = Category::create(['slug' => 'backend', 'label' => 'Backend']);
        $this->catFrontend = Category::create(['slug' => 'frontend', 'label' => 'Frontend']);
    }

    public function test_list_published_returns_only_published_and_past_articles(): void
    {
        // 1. Article publié dans le passé
        Article::create([
            'title' => 'Article Passé',
            'slug' => 'article-passe',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);

        // 2. Article publié avec date nulle (considéré immédiat)
        Article::create([
            'title' => 'Article Immédiat',
            'slug' => 'article-immediat',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => null,
        ]);

        // 3. Article publié mais planifié dans le futur (ne doit pas apparaître)
        Article::create([
            'title' => 'Article Futur',
            'slug' => 'article-futur',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->addDay(),
        ]);

        // 4. Article brouillon (ne doit pas apparaître)
        Article::create([
            'title' => 'Article Brouillon',
            'slug' => 'article-brouillon',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Draft,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished();

        $this->assertCount(2, $results->items());
        $this->assertEqualsCanonicalizing(['article-passe', 'article-immediat'], collect($results->items())->pluck('slug')->all());
    }

    public function test_list_published_filters_by_category(): void
    {
        Article::create([
            'title' => 'Article Backend',
            'slug' => 'article-backend',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);

        Article::create([
            'title' => 'Article Frontend',
            'slug' => 'article-frontend',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catFrontend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished(category: 'backend');

        $this->assertCount(1, $results->items());
        $this->assertEquals('article-backend', collect($results->items())->first()?->slug);
    }

    public function test_list_published_filters_by_tag(): void
    {
        $tagPhp = Tag::create(['name' => 'PHP']);
        $tagJs = Tag::create(['name' => 'JS']);

        $artPhp = Article::create([
            'title' => 'Article PHP',
            'slug' => 'article-php',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artPhp->tags()->sync([$tagPhp->id]);

        $artJs = Article::create([
            'title' => 'Article JS',
            'slug' => 'article-js',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artJs->tags()->sync([$tagJs->id]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished(tag: 'PHP');

        $this->assertCount(1, $results->items());
        $this->assertEquals('article-php', collect($results->items())->first()?->slug);
    }

    public function test_list_published_orders_by_published_at_descending(): void
    {
        $oldest = Article::create([
            'title' => 'Oldest',
            'slug' => 'oldest',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDays(5),
        ]);

        $newest = Article::create([
            'title' => 'Newest',
            'slug' => 'newest',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subHour(),
        ]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished();

        $this->assertEquals('newest', collect($results->items())->first()?->slug);
        $this->assertEquals('oldest', collect($results->items())->last()?->slug);
    }

    public function test_list_published_respects_pagination(): void
    {
        // Création de 15 articles publiés
        for ($i = 1; $i <= 15; $i++) {
            Article::create([
                'title' => "Article {$i}",
                'slug' => "article-{$i}",
                'excerpt' => 'Intro',
                'content' => 'Corps',
                'category_id' => $this->catBackend->id,
                'status' => ArticleStatus::Published,
                'reading_time' => 3,
                'published_at' => now()->subDays($i),
            ]);
        }

        /** @var LengthAwarePaginator<int, Article> $page1 */
        $page1 = $this->articleService->listPublished(page: 1, pageSize: 5);
        $this->assertCount(5, $page1->items());
        $this->assertEquals(15, $page1->total());
        $this->assertEquals(1, $page1->currentPage());

        /** @var LengthAwarePaginator<int, Article> $page3 */
        $page3 = $this->articleService->listPublished(page: 3, pageSize: 5);
        $this->assertCount(5, $page3->items());
        $this->assertEquals(3, $page3->currentPage());
    }

    public function test_find_by_slug_returns_published_article(): void
    {
        Article::create([
            'title' => 'My Article',
            'slug' => 'my-article',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);

        $found = $this->articleService->findBySlug('my-article');

        $this->assertNotNull($found);
        $this->assertEquals('My Article', $found->title);
    }

    public function test_find_by_slug_returns_null_for_draft_article(): void
    {
        Article::create([
            'title' => 'My Draft',
            'slug' => 'my-draft',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->catBackend->id,
            'status' => ArticleStatus::Draft,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);

        $found = $this->articleService->findBySlug('my-draft');

        $this->assertNull($found);
    }

    public function test_find_by_slug_returns_null_when_article_not_found(): void
    {
        $found = $this->articleService->findBySlug('non-existent');

        $this->assertNull($found);
    }
}
