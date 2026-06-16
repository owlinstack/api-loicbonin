<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'slug' => 'backend',
            'label' => 'Backend',
        ]);
    }

    public function test_can_list_published_articles_only(): void
    {
        $published = Article::create([
            'title' => 'Article publié',
            'slug' => 'article-publie',
            'excerpt' => 'Introduction',
            'content' => 'Contenu complet',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'featured' => false,
            'published_at' => now()->subDay(),
        ]);

        Article::create([
            'title' => 'Article brouillon',
            'slug' => 'article-brouillon',
            'excerpt' => 'Introduction draft',
            'content' => 'Contenu draft',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Draft,
            'reading_time' => 3,
            'featured' => false,
            'published_at' => null,
        ]);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'articles' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'content',
                        'category',
                        'tags',
                        'publishedAt',
                        'readingTime',
                        'featured',
                    ]
                ],
                'total',
                'page',
                'pageSize',
            ]);

        $response->assertJsonFragment(['slug' => 'article-publie']);
        $response->assertJsonMissing(['slug' => 'article-brouillon']);
    }

    public function test_can_get_single_published_article(): void
    {
        $tag = Tag::create(['name' => 'Laravel']);

        $article = Article::create([
            'title' => 'Mon Article',
            'slug' => 'mon-article',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 4,
            'featured' => true,
            'published_at' => now()->subHour(),
        ]);

        $article->tags()->sync([$tag->id]);

        $response = $this->getJson('/api/v1/articles/mon-article');

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Mon Article')
            ->assertJsonPath('slug', 'mon-article')
            ->assertJsonPath('category', 'backend')
            ->assertJsonPath('tags.0', 'Laravel');
    }

    public function test_cannot_get_draft_article(): void
    {
        Article::create([
            'title' => 'Brouillon',
            'slug' => 'brouillon',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Draft,
            'reading_time' => 2,
            'published_at' => null,
        ]);

        $response = $this->getJson('/api/v1/articles/brouillon');

        $response->assertStatus(404);
    }
}
