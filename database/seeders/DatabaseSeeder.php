<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        User::firstOrCreate(
            ['email' => 'admin@loicbonin.com'],
            [
                'name' => 'Loïc Bonin',
                'password' => Hash::make('password'),
            ]
        );

        // 1.5 Profile
        Profile::firstOrCreate(
            ['name' => 'Loïc Bonin'],
            [
                'bio' => 'Développeur full-stack basé à Paris. Je construis des interfaces de lecture, des outils pour équipes éditoriales et des systèmes de synchronisation de données. Passionné par la typographie, les standards du web et les DX raisonnables.',
                'skills' => [
                    [
                        'term' => 'Frontend',
                        'description' => "React, Next.js, TypeScript strict, CSS moderne (Container Queries, View Transitions, Layers). Sensible à la performance perçue et à l'accessibilité.",
                    ],
                    [
                        'term' => 'Backend',
                        'description' => 'Node.js, Drizzle ORM, PostgreSQL (Neon), Cloudflare Workers. Architecture orientée data pipelines plutôt que microservices.',
                    ],
                    [
                        'term' => 'Outillage',
                        'description' => "Vitest, Playwright, Turborepo, GitHub Actions. Préférence pour des chaînes simples et compréhensibles plutôt qu'exhaustives.",
                    ],
                    [
                        'term' => 'Veille',
                        'description' => 'Suivi régulier des RFC TC39, WICG et CSSWG. Lecture de spécifications en source primaire plutôt que de résumés.',
                    ],
                ],
                'timeline' => [
                    [
                        'date' => '2024 — présent',
                        'title' => 'Développeur indépendant',
                        'description' => "Missions d'architecture frontend et conseil technique pour startups early-stage.",
                    ],
                    [
                        'date' => '2022 — 2024',
                        'title' => 'Lead Frontend, Contentsquare',
                        'description' => 'Refonte du design system, migration vers App Router, réduction de 40% du bundle JS.',
                    ],
                    [
                        'date' => '2019 — 2022',
                        'title' => 'Développeur Full-stack, Alan',
                        'description' => 'Interfaces patient et back-office médical. First employee engineering.',
                    ],
                    [
                        'date' => '2017 — 2019',
                        'title' => 'Master Informatique, EPITA',
                        'description' => 'Spécialisation systèmes et réseaux.',
                    ],
                ],
                'cv_url' => null,
            ]
        );

        // 2. Categories
        $categoriesData = [
            'react' => 'React',
            'typescript' => 'TypeScript',
            'css' => 'CSS',
            'backend' => 'Backend',
            'tooling' => 'Tooling',
        ];

        $categories = [];
        foreach ($categoriesData as $slug => $label) {
            $categories[$slug] = Category::firstOrCreate(
                ['slug' => $slug],
                ['label' => $label]
            );
        }

        // 3. Tags
        $tagsData = ['Next.js', 'React', 'Tailwind CSS', 'Laravel', 'PostgreSQL', 'SQLite', 'PHP', 'TypeScript', 'Filament'];
        $tags = [];
        foreach ($tagsData as $name) {
            $tags[$name] = Tag::firstOrCreate(['name' => $name]);
        }

        // 4. Projects
        $project = Project::firstOrCreate(
            ['slug' => 'loicbonin-com'],
            [
                'title' => 'loicbonin.com',
                'description' => 'Mon portfolio de pointe en Next.js App Router & Laravel 13 API.',
                'long_description' => 'Un portfolio conçu comme une vitrine technologique complète exposant mes compétences avec une UI premium, un éditeur de code virtuel interactif et un système de blog technique administrable sous Filament v5.',
                'tech_stack' => ['Next.js', 'React', 'Tailwind CSS', 'Laravel', 'Filament', 'SQLite'],
                'live_url' => 'https://loicbonin.com',
                'repo_url' => 'https://github.com/loicbonin/loicbonin.com',
                'featured' => true,
            ]
        );

        // 5. Articles
        $article = Article::firstOrCreate(
            ['slug' => 'architecture-modulaire-filament-v5-laravel'],
            [
                'title' => 'Architecture modulaire de Filament v5 dans Laravel',
                'excerpt' => 'Découvrez comment Filament v5 révolutionne l\'écriture des interfaces d\'administration avec son nouveau système unifié de Schemas.',
                'content' => "Filament v5 introduit un changement majeur dans la manière d'écrire des formulaires et des vues de détails (infolists) en unifiant leur fonctionnement sous la classe `Schema`.\n\nCela permet une plus grande flexibilité, de meilleures performances de rendu avec Livewire v4, et une modularité totale.\n\nDans cet article, nous décrivons la mise en place d'un PanelProvider moderne et la structure des composants.",
                'category_id' => $categories['backend']->id,
                'status' => ArticleStatus::Published,
                'reading_time' => 5,
                'featured' => true,
                'published_at' => now(),
            ]
        );

        // Link tags to article
        $article->tags()->sync([
            $tags['Laravel']->id,
            $tags['PHP']->id,
            $tags['Filament']->id,
        ]);

        // 6. Code Folders
        $appFolder = CodeFolder::firstOrCreate(
            ['path' => 'app'],
            ['name' => 'app', 'sort_order' => 10]
        );

        $providersFolder = CodeFolder::firstOrCreate(
            ['path' => 'app/Providers'],
            ['name' => 'Providers', 'parent_id' => $appFolder->id, 'sort_order' => 20]
        );

        $filamentFolder = CodeFolder::firstOrCreate(
            ['path' => 'app/Providers/Filament'],
            ['name' => 'Filament', 'parent_id' => $providersFolder->id, 'sort_order' => 30]
        );

        // 7. Code Files
        CodeFile::firstOrCreate(
            ['path' => 'app/Providers/Filament/AdminCreatorPanelProvider.php'],
            [
                'name' => 'AdminCreatorPanelProvider.php',
                'language' => 'php',
                'content' => <<<'PHP'
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminCreatorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin-creator')
            ->path('admin-creator')
            ->login()
            // ->multifactorAuthentication()
            ->colors([
                'primary' => '#01696f',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ]);
    }
}
PHP
                ,
                'folder_id' => $filamentFolder->id,
                'linked_article_id' => $article->id,
                'sort_order' => 10,
            ]
        );
    }
}
