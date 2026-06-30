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
use Illuminate\Support\Facades\DB;
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
        $art1 = Article::create([
            'title' => 'Article Passé',
            'slug' => 'article-passe',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $art1->categories()->sync([$this->catBackend->id]);

        // 2. Article publié avec date nulle (considéré immédiat)
        $art2 = Article::create([
            'title' => 'Article Immédiat',
            'slug' => 'article-immediat',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => null,
        ]);
        $art2->categories()->sync([$this->catBackend->id]);

        // 3. Article publié mais planifié dans le futur (ne doit pas apparaître)
        $art3 = Article::create([
            'title' => 'Article Futur',
            'slug' => 'article-futur',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->addDay(),
        ]);
        $art3->categories()->sync([$this->catBackend->id]);

        // 4. Article brouillon (ne doit pas apparaître)
        $art4 = Article::create([
            'title' => 'Article Brouillon',
            'slug' => 'article-brouillon',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Draft,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $art4->categories()->sync([$this->catBackend->id]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished();

        $this->assertCount(2, $results->items());
        $this->assertEqualsCanonicalizing(['article-passe', 'article-immediat'], collect($results->items())->pluck('slug')->all());
    }

    public function test_list_published_excludes_archived_articles(): void
    {
        $artArchived = Article::create([
            'title' => 'Article Archivé',
            'slug' => 'article-archive',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Archived,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artArchived->categories()->sync([$this->catBackend->id]);

        $artPub = Article::create([
            'title' => 'Article Publié',
            'slug' => 'article-publie',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artPub->categories()->sync([$this->catBackend->id]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished();

        $this->assertCount(1, $results->items());
        $this->assertEquals('article-publie', collect($results->items())->first()?->slug);
    }

    public function test_list_published_filters_by_category_and_tag_combined(): void
    {
        $tagPhp = Tag::create(['name' => 'PHP']);
        $tagJs = Tag::create(['name' => 'JS']);

        // Backend + PHP : doit apparaître
        // Backend + PHP : doit apparaître
        $artBackendPhp = Article::create([
            'title' => 'Backend PHP',
            'slug' => 'backend-php',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artBackendPhp->categories()->sync([$this->catBackend->id]);
        $artBackendPhp->tags()->sync([$tagPhp->id]);

        // Backend + JS : ne doit pas apparaître (mauvais tag)
        $artBackendJs = Article::create([
            'title' => 'Backend JS',
            'slug' => 'backend-js',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artBackendJs->categories()->sync([$this->catBackend->id]);
        $artBackendJs->tags()->sync([$tagJs->id]);

        // Frontend + PHP : ne doit pas apparaître (mauvaise catégorie)
        $artFrontendPhp = Article::create([
            'title' => 'Frontend PHP',
            'slug' => 'frontend-php',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artFrontendPhp->categories()->sync([$this->catFrontend->id]);
        $artFrontendPhp->tags()->sync([$tagPhp->id]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished(category: 'backend', tag: 'PHP');

        $this->assertCount(1, $results->items());
        $this->assertEquals('backend-php', collect($results->items())->first()?->slug);
    }

    public function test_list_published_filters_by_category(): void
    {
        $artBackend = Article::create([
            'title' => 'Article Backend',
            'slug' => 'article-backend',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artBackend->categories()->sync([$this->catBackend->id]);

        $artFrontend = Article::create([
            'title' => 'Article Frontend',
            'slug' => 'article-frontend',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artFrontend->categories()->sync([$this->catFrontend->id]);

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
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artPhp->categories()->sync([$this->catBackend->id]);
        $artPhp->tags()->sync([$tagPhp->id]);

        $artJs = Article::create([
            'title' => 'Article JS',
            'slug' => 'article-js',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $artJs->categories()->sync([$this->catBackend->id]);
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
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDays(5),
        ]);
        $oldest->categories()->sync([$this->catBackend->id]);

        $newest = Article::create([
            'title' => 'Newest',
            'slug' => 'newest',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subHour(),
        ]);
        $newest->categories()->sync([$this->catBackend->id]);

        /** @var LengthAwarePaginator<int, Article> $results */
        $results = $this->articleService->listPublished();

        $this->assertEquals('newest', collect($results->items())->first()?->slug);
        $this->assertEquals('oldest', collect($results->items())->last()?->slug);
    }

    public function test_list_published_respects_pagination(): void
    {
        // Création de 15 articles publiés
        for ($i = 1; $i <= 15; $i++) {
            $art = Article::create([
                'title' => "Article {$i}",
                'slug' => "article-{$i}",
                'excerpt' => 'Intro',
                'content' => 'Corps',
                'status' => ArticleStatus::Published,
                'reading_time' => 3,
                'published_at' => now()->subDays($i),
            ]);
            $art->categories()->sync([$this->catBackend->id]);
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
        $art = Article::create([
            'title' => 'My Article',
            'slug' => 'my-article',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $art->categories()->sync([$this->catBackend->id]);

        $found = $this->articleService->findBySlug('my-article');

        $this->assertNotNull($found);
        $this->assertEquals('My Article', $found->title);
    }

    public function test_find_by_slug_returns_null_for_draft_article(): void
    {
        $draft = Article::create([
            'title' => 'My Draft',
            'slug' => 'my-draft',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Draft,
            'reading_time' => 3,
            'published_at' => now()->subDay(),
        ]);
        $draft->categories()->sync([$this->catBackend->id]);

        $found = $this->articleService->findBySlug('my-draft');

        $this->assertNull($found);
    }

    public function test_find_by_slug_returns_null_when_article_not_found(): void
    {
        $found = $this->articleService->findBySlug('non-existent');

        $this->assertNull($found);
    }

    public function test_find_by_slug_returns_null_for_future_scheduled_article(): void
    {
        $future = Article::create([
            'title' => 'Article Futur',
            'slug' => 'article-futur',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->addDay(),
        ]);
        $future->categories()->sync([$this->catBackend->id]);

        // Un article publié mais planifié dans le futur ne doit pas être accessible
        $found = $this->articleService->findBySlug('article-futur');

        $this->assertNull($found);
    }

    public function test_list_published_uses_default_page_size(): void
    {
        Article::query()->delete();
        for ($i = 1; $i <= 15; $i++) {
            $art = Article::create([
                'title' => "Article {$i}",
                'slug' => "article-{$i}",
                'excerpt' => 'Intro',
                'content' => 'Corps',
                'status' => ArticleStatus::Published,
                'reading_time' => 3,
                'published_at' => now()->subDays($i),
            ]);
            $art->categories()->sync([$this->catBackend->id]);
        }

        $results = $this->articleService->listPublished();
        $this->assertCount(10, $results->items());
        $this->assertEquals(15, $results->total());
    }

    public function test_list_published_eager_loads_relations_without_n_plus_one(): void
    {
        Article::query()->delete();

        // 1. Charger avec 2 articles
        for ($i = 1; $i <= 2; $i++) {
            $art = Article::create([
                'title' => "Article {$i}",
                'slug' => "article-{$i}",
                'excerpt' => 'Intro',
                'content' => 'Corps',
                'status' => ArticleStatus::Published,
                'reading_time' => 3,
                'published_at' => now()->subDays($i),
            ]);
            $art->categories()->sync([$this->catBackend->id]);
            $art->tags()->sync([Tag::create(['name' => "Tag {$i}"])->id]);
        }

        DB::enableQueryLog();
        $results2 = $this->articleService->listPublished();
        foreach ($results2->items() as $article) {
            foreach ($article->categories as $category) {
                $category->label;
            }
            $article->tags->pluck('name');
        }
        $queriesForTwo = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 2. Charger avec 7 articles (5 supplémentaires)
        for ($i = 3; $i <= 7; $i++) {
            $art = Article::create([
                'title' => "Article {$i}",
                'slug' => "article-{$i}",
                'excerpt' => 'Intro',
                'content' => 'Corps',
                'status' => ArticleStatus::Published,
                'reading_time' => 3,
                'published_at' => now()->subDays($i),
            ]);
            $art->categories()->sync([$this->catBackend->id]);
            $art->tags()->sync([Tag::create(['name' => "Tag {$i}"])->id]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $results7 = $this->articleService->listPublished();
        foreach ($results7->items() as $article) {
            foreach ($article->categories as $category) {
                $category->label;
            }
            $article->tags->pluck('name');
        }
        $queriesForSeven = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Le nombre de requêtes doit être identique s'il y a eager loading (évite les requêtes N+1)
        $this->assertEquals($queriesForTwo, $queriesForSeven);
    }
}
