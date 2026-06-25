<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\CodeProject;
use App\Models\Tag;
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
                        'codeFile',
                        'codeFolder',
                        'codeProject',
                    ],
                ],
                'total',
                'page',
                'pageSize',
            ]);

        $response->assertJsonFragment(['slug' => 'article-publie']);
        $response->assertJsonMissing(['slug' => 'article-brouillon']);
    }

    public function test_list_does_not_return_future_scheduled_articles(): void
    {
        Article::create([
            'title' => 'Article Futur',
            'slug' => 'article-futur',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->addDay(),
        ]);

        Article::create([
            'title' => 'Article Visible',
            'slug' => 'article-visible',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->subHour(),
        ]);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)
            ->assertJsonFragment(['slug' => 'article-visible'])
            ->assertJsonMissing(['slug' => 'article-futur']);
    }

    public function test_can_filter_articles_by_category(): void
    {
        $otherCategory = Category::create([
            'slug' => 'frontend',
            'label' => 'Frontend',
        ]);

        Article::create([
            'title' => 'Article Backend',
            'slug' => 'article-backend',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'featured' => false,
            'published_at' => now()->subDay(),
        ]);

        Article::create([
            'title' => 'Article Frontend',
            'slug' => 'article-frontend',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $otherCategory->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'featured' => false,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/v1/articles?category=backend');

        $response->assertStatus(200)
            ->assertJsonFragment(['slug' => 'article-backend'])
            ->assertJsonMissing(['slug' => 'article-frontend']);
    }

    public function test_can_filter_articles_by_tag(): void
    {
        $tagPhp = Tag::create(['name' => 'PHP']);
        $tagJs = Tag::create(['name' => 'JS']);

        $articlePhp = Article::create([
            'title' => 'Article PHP',
            'slug' => 'article-php',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'featured' => false,
            'published_at' => now()->subDay(),
        ]);
        $articlePhp->tags()->sync([$tagPhp->id]);

        $articleJs = Article::create([
            'title' => 'Article JS',
            'slug' => 'article-js',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'featured' => false,
            'published_at' => now()->subDay(),
        ]);
        $articleJs->tags()->sync([$tagJs->id]);

        $response = $this->getJson('/api/v1/articles?tag=PHP');

        $response->assertStatus(200)
            ->assertJsonFragment(['slug' => 'article-php'])
            ->assertJsonMissing(['slug' => 'article-js']);
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

    public function test_cannot_get_future_scheduled_article(): void
    {
        Article::create([
            'title' => 'Article Futur',
            'slug' => 'article-futur',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now()->addDay(),
        ]);

        // Un article planifié dans le futur ne doit pas être accessible via l'endpoint
        $response = $this->getJson('/api/v1/articles/article-futur');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Article not found');
    }

    public function test_article_returns_code_file_relationship(): void
    {
        $folder = CodeFolder::create([
            'name' => 'app',
            'path' => 'app',
        ]);

        $file = CodeFile::create([
            'name' => 'test.php',
            'path' => 'app/test.php',
            'language' => 'php',
            'content' => '<?php echo "hello";',
            'folder_id' => $folder->id,
        ]);

        $article = Article::create([
            'title' => 'Article with File',
            'slug' => 'article-with-file',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'published_at' => now()->subDay(),
            'code_file_id' => $file->id,
        ]);

        $response = $this->getJson('/api/v1/articles/article-with-file');

        $response->assertStatus(200)
            ->assertJsonPath('codeFile.name', 'test.php')
            ->assertJsonPath('codeFile.path', 'app/test.php')
            ->assertJsonPath('codeFolder', null)
            ->assertJsonPath('codeProject', null);
    }

    public function test_article_returns_code_folder_tree_relationship(): void
    {
        $parentFolder = CodeFolder::create([
            'name' => 'app',
            'path' => 'app',
        ]);

        $childFolder = CodeFolder::create([
            'name' => 'Http',
            'path' => 'app/Http',
            'parent_id' => $parentFolder->id,
        ]);

        CodeFile::create([
            'name' => 'Kernel.php',
            'path' => 'app/Http/Kernel.php',
            'language' => 'php',
            'content' => 'class Kernel {}',
            'folder_id' => $childFolder->id,
        ]);

        $article = Article::create([
            'title' => 'Article with Folder',
            'slug' => 'article-with-folder',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'published_at' => now()->subDay(),
            'code_folder_id' => $parentFolder->id,
        ]);

        $response = $this->getJson('/api/v1/articles/article-with-folder');

        $response->assertStatus(200)
            ->assertJsonPath('codeFile', null)
            ->assertJsonPath('codeFolder.name', 'app')
            ->assertJsonPath('codeFolder.children.0.name', 'Http')
            ->assertJsonPath('codeFolder.children.0.children.0.name', 'Kernel.php')
            ->assertJsonPath('codeProject', null);
    }

    public function test_article_returns_code_project_tree_relationship(): void
    {
        $project = CodeProject::create([
            'name' => 'My Project',
            'slug' => 'my-project',
            'description' => 'A test project',
        ]);

        $folder = CodeFolder::create([
            'name' => 'app',
            'path' => 'app',
            'code_project_id' => $project->id,
        ]);

        CodeFile::create([
            'name' => 'index.php',
            'path' => 'app/index.php',
            'language' => 'php',
            'content' => 'echo "hello";',
            'folder_id' => $folder->id,
        ]);

        $article = Article::create([
            'title' => 'Article with Project',
            'slug' => 'article-with-project',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $this->category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 5,
            'published_at' => now()->subDay(),
            'code_project_id' => $project->id,
        ]);

        $response = $this->getJson('/api/v1/articles/article-with-project');

        $response->assertStatus(200)
            ->assertJsonPath('codeFile', null)
            ->assertJsonPath('codeFolder', null)
            ->assertJsonPath('codeProject.name', 'My Project')
            ->assertJsonPath('codeProject.tree.0.name', 'app')
            ->assertJsonPath('codeProject.tree.0.children.0.name', 'index.php');
    }

    public function test_get_non_existent_article_returns_404(): void
    {
        $response = $this->getJson('/api/v1/articles/does-not-exist');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Article not found');
    }
}
