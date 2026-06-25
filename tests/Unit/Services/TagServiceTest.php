<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour TagService.
 * Justification : Valide l'accès à plat aux noms de tags de la base de données.
 */
final class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagService $tagService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = new TagService();
    }

    /**
     * Teste que la méthode listAllNames retourne la liste plate des noms des tags en base.
     */
    public function test_list_all_names_returns_flat_array_of_tag_names(): void
    {
        Tag::create(['name' => 'Laravel']);
        Tag::create(['name' => 'Docker']);

        $names = $this->tagService->listAllNames();

        $this->assertCount(2, $names);
        $this->assertEqualsCanonicalizing(['Laravel', 'Docker'], $names->all());
    }
}
