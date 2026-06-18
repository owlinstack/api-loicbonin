<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\CodeProject;
use App\Models\Profile;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GeneralApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories(): void
    {
        Category::create(['slug' => 'react', 'label' => 'React']);
        Category::create(['slug' => 'tooling', 'label' => 'Tooling']);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'slug',
                    'label',
                    'count',
                ],
            ]);

        $response->assertJsonFragment(['slug' => 'react']);
    }

    public function test_can_list_tags(): void
    {
        Tag::create(['name' => 'Next.js']);
        Tag::create(['name' => 'Laravel']);

        $response = $this->getJson('/api/v1/tags');

        $response->assertStatus(200);
        $this->assertEqualsCanonicalizing(['Next.js', 'Laravel'], $response->json());
    }

    public function test_can_get_profile(): void
    {
        Profile::create([
            'name' => 'Loïc de Test',
            'bio' => 'Ma Bio de test',
            'skills' => [
                ['term' => 'HTML', 'description' => 'Super Skill'],
            ],
            'timeline' => [
                ['date' => '2026', 'title' => 'Test Job', 'description' => 'Job description'],
            ],
            'education' => [
                ['date' => '2019', 'title' => 'EPITA', 'description' => 'Diplôme info'],
            ],
            'cv_url' => 'cvs/test-cv.pdf',
            'avatar_url' => 'avatars/test-avatar.jpg',
        ]);

        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'bio',
                'skills' => [
                    '*' => [
                        'term',
                        'description',
                    ],
                ],
                'timeline' => [
                    '*' => [
                        'date',
                        'title',
                        'description',
                    ],
                ],
                'education' => [
                    '*' => [
                        'date',
                        'title',
                        'description',
                    ],
                ],
                'cvUrl',
                'avatarUrl',
            ])
            ->assertJsonPath('name', 'Loïc de Test')
            ->assertJsonPath('bio', 'Ma Bio de test')
            ->assertJsonPath('skills.0.term', 'HTML')
            ->assertJsonPath('timeline.0.title', 'Test Job')
            ->assertJsonPath('education.0.title', 'EPITA')
            ->assertJsonPath('avatarUrl', asset('storage/avatars/test-avatar.jpg'));
    }

    public function test_get_profile_returns_fallback_when_empty_db(): void
    {
        Profile::query()->delete();

        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'bio',
                'skills' => [
                    '*' => [
                        'term',
                        'description',
                    ],
                ],
                'timeline' => [
                    '*' => [
                        'date',
                        'title',
                        'description',
                    ],
                ],
                'education' => [
                    '*' => [
                        'date',
                        'title',
                        'description',
                    ],
                ],
                'cvUrl',
                'avatarUrl',
            ])
            ->assertJsonPath('name', 'Loïc Bonin')
            ->assertJsonPath('bio', "Développeur full-stack basé à Lyon. Depuis 2016, je conçois et développe des applications web responsive. Spécialisé en PHP et TypeScript, j'ai aussi de l'expérience OPS en déploiement et gestion de serveurs dédiées. Appétence pour le mentorat et compétences pédagogiques, je pratique une intégration pragmatique et réfléchie des outils IA pour chaque besoin et contrainte de projet.(SDD/Context Engineering)");
    }

    public function test_articles_list_validates_query_parameters(): void
    {
        $responseNegativePage = $this->getJson('/api/v1/articles?page=-1');
        $responseNegativePage->assertStatus(422)
            ->assertJsonValidationErrors(['page']);

        $responseHugePageSize = $this->getJson('/api/v1/articles?pageSize=150');
        $responseHugePageSize->assertStatus(422)
            ->assertJsonValidationErrors(['pageSize']);

        $responseStringPage = $this->getJson('/api/v1/articles?page=abc');
        $responseStringPage->assertStatus(422)
            ->assertJsonValidationErrors(['page']);
    }

    public function test_can_get_code_tree_and_files(): void
    {
        $category = Category::create(['slug' => 'backend', 'label' => 'Backend']);
        $article = Article::create([
            'title' => 'Article Code',
            'slug' => 'article-code',
            'excerpt' => 'Intro',
            'content' => 'Corps',
            'category_id' => $category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now(),
        ]);

        $folder = CodeFolder::create([
            'name' => 'app',
            'path' => 'app',
            'sort_order' => 1,
        ]);

        $file = CodeFile::create([
            'name' => 'Helper.php',
            'path' => 'app/Helper.php',
            'language' => 'php',
            'content' => '<?php echo "hello";',
            'folder_id' => $folder->id,
            'sort_order' => 1,
        ]);

        $article->update(['code_file_id' => $file->id]);

        $treeResponse = $this->getJson('/api/v1/code/tree');

        $treeResponse->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'app',
                'path' => 'app',
            ]);

        $fileResponse = $this->getJson('/api/v1/code/files/app/Helper.php');

        $fileResponse->assertStatus(200)
            ->assertJsonPath('name', 'Helper.php')
            ->assertJsonPath('path', 'app/Helper.php')
            ->assertJsonPath('language', 'php')
            ->assertJsonPath('content', '<?php echo "hello";')
            ->assertJsonPath('linkedArticleSlug', 'article-code');
    }

    public function test_get_non_existent_code_file_returns_404(): void
    {
        $response = $this->getJson('/api/v1/code/files/non-existent.php');
        $response->assertStatus(404);
    }

    public function test_can_list_code_projects_and_get_tree(): void
    {
        $project = CodeProject::create([
            'name' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'Test project description',
        ]);

        $folder = CodeFolder::create([
            'name' => 'src',
            'path' => 'src',
            'code_project_id' => $project->id,
            'sort_order' => 1,
        ]);

        CodeFile::create([
            'name' => 'main.js',
            'path' => 'src/main.js',
            'language' => 'javascript',
            'content' => 'console.log("hello");',
            'folder_id' => $folder->id,
            'sort_order' => 1,
        ]);

        // 1. Test listing
        $listResponse = $this->getJson('/api/v1/code/projects');
        $listResponse->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Project Alpha',
                'slug' => 'project-alpha',
                'description' => 'Test project description',
            ]);

        // 2. Test tree
        $treeResponse = $this->getJson('/api/v1/code/projects/project-alpha/tree');
        $treeResponse->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'src',
                'path' => 'src',
            ]);
    }
}
