<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\CodeProject;
use App\Services\CodeTreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CodeTreeServiceTest extends TestCase
{
    use RefreshDatabase;

    private CodeTreeService $codeTreeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codeTreeService = new CodeTreeService;
    }

    public function test_get_full_tree_builds_correct_nested_structure(): void
    {
        // 1. Catégorie et Article
        $category = Category::create(['slug' => 'backend', 'label' => 'Backend']);
        $article = Article::create([
            'title' => 'Article Code',
            'slug' => 'article-code',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'category_id' => $category->id,
            'status' => ArticleStatus::Published,
            'reading_time' => 3,
            'published_at' => now(),
        ]);

        // 2. Dossier parent
        /** @var CodeFolder $folderApp */
        $folderApp = CodeFolder::create([
            'name' => 'app',
            'path' => 'app',
            'sort_order' => 1,
        ]);

        // 3. Fichier dans dossier parent avec article lié
        /** @var CodeFile $fileHelper */
        $fileHelper = CodeFile::create([
            'name' => 'Helper.php',
            'path' => 'app/Helper.php',
            'language' => 'php',
            'content' => '<?php echo "helper";',
            'folder_id' => $folderApp->id,
            'sort_order' => 2, // après le sous-dossier
        ]);

        $article->update(['code_file_id' => $fileHelper->id]);

        // 4. Dossier enfant
        /** @var CodeFolder $folderHttp */
        $folderHttp = CodeFolder::create([
            'name' => 'Http',
            'path' => 'app/Http',
            'parent_id' => $folderApp->id,
            'sort_order' => 1, // avant Helper.php
        ]);

        // 5. Fichier dans dossier enfant
        CodeFile::create([
            'name' => 'Kernel.php',
            'path' => 'app/Http/Kernel.php',
            'language' => 'php',
            'content' => 'class Kernel {}',
            'folder_id' => $folderHttp->id,
            'sort_order' => 1,
        ]);

        $tree = $this->codeTreeService->getFullTree();

        // On attend:
        // Index 0: Dossier 'app'
        $this->assertCount(1, $tree);

        $appNode = $tree[0];
        $this->assertEquals('app', $appNode['name']);
        $this->assertEquals('app', $appNode['path']);

        // Dans 'app', on doit avoir par sort_order:
        // Index 0: Dossier 'Http'
        // Index 1: Fichier 'Helper.php'
        $this->assertCount(2, $appNode['children']);

        $httpNode = $appNode['children'][0];
        $this->assertEquals('Http', $httpNode['name']);
        $this->assertEquals('app/Http', $httpNode['path']);
        $this->assertCount(1, $httpNode['children']);
        $this->assertEquals('Kernel.php', $httpNode['children'][0]['name']);

        $fileNode = $appNode['children'][1];
        $this->assertEquals('Helper.php', $fileNode['name']);
        $this->assertEquals('app/Helper.php', $fileNode['path']);
        $this->assertEquals('article-code', $fileNode['linkedArticleSlug']);
        $this->assertEquals('Article Code', $fileNode['linkedArticleTitle']);
    }

    public function test_get_project_tree_filters_exclusively_by_project(): void
    {
        // 1. Projets
        $projectA = CodeProject::create([
            'name' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'Alpha desc',
        ]);

        $projectB = CodeProject::create([
            'name' => 'Project Beta',
            'slug' => 'project-beta',
            'description' => 'Beta desc',
        ]);

        // 2. Dossiers Project A
        $folderSrcA = CodeFolder::create([
            'name' => 'src',
            'path' => 'src',
            'code_project_id' => $projectA->id,
            'sort_order' => 1,
        ]);

        $folderSubA = CodeFolder::create([
            'name' => 'components',
            'path' => 'src/components',
            'parent_id' => $folderSrcA->id,
            'sort_order' => 1,
        ]);

        CodeFile::create([
            'name' => 'Button.tsx',
            'path' => 'src/components/Button.tsx',
            'language' => 'typescript',
            'content' => 'export const Button = () => null;',
            'folder_id' => $folderSubA->id,
            'sort_order' => 1,
        ]);

        // 3. Dossiers Project B (ne doivent pas être dans l'arbre de A)
        $folderSrcB = CodeFolder::create([
            'name' => 'app',
            'path' => 'app',
            'code_project_id' => $projectB->id,
            'sort_order' => 1,
        ]);

        CodeFile::create([
            'name' => 'index.php',
            'path' => 'app/index.php',
            'language' => 'php',
            'content' => '<?php echo "b";',
            'folder_id' => $folderSrcB->id,
            'sort_order' => 1,
        ]);

        $treeA = $this->codeTreeService->getProjectTree($projectA);

        // On attend seulement les fichiers/dossiers de Project A
        $this->assertCount(1, $treeA);
        $this->assertEquals('src', $treeA[0]['name']);
        $this->assertEquals('components', $treeA[0]['children'][0]['name']);
        $this->assertEquals('Button.tsx', $treeA[0]['children'][0]['children'][0]['name']);

        // Vérification qu'on a aucun dossier de B
        $treeB = $this->codeTreeService->getProjectTree($projectB);
        $this->assertCount(1, $treeB);
        $this->assertEquals('app', $treeB[0]['name']);
        $this->assertEquals('index.php', $treeB[0]['children'][0]['name']);
    }
}
