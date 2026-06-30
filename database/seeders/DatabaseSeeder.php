<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\CodeProject;
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
                'bio' => "Développeur full-stack basé à Lyon. Depuis 2016, je conçois et développe des applications web responsive. Spécialisé en PHP et TypeScript, j'ai aussi de l'expérience OPS en déploiement et gestion de serveurs dédiées. Appétence pour le mentorat et compétences pédagogiques, je pratique une intégration pragmatique et réfléchie des outils IA pour chaque besoin et contrainte de projet.(SDD/Context Engineering)",
                'skills' => [
                    [
                        'term' => 'Frontend',
                        'description' => 'Vue.JS, Nuxt.JS, React, Next.js, TypeScript strict, CSS moderne. Sensible à la performance perçue et à l\'accessibilité.',
                    ],
                    [
                        'term' => 'Backend',
                        'description' => 'Symfony, Laravel, Sylius, Durpal, Node.js, PayloadCMS, StrapiCMS, Django. Approche architecture sur mesure.',
                    ],
                    [
                        'term' => 'Outillage',
                        'description' => 'Git, GitHub Actions, Docker, IDE, Debian, MacOS, Nginx, Systemd... Approche pragmatique et robuste de l\'outillage.',
                    ],
                    [
                        'term' => 'Veille',
                        'description' => 'Suivi régulier des évolutions des outils, normes et Framework via source primaire (documentation). Veille active journalière sur l\'utilisation des outils IA.',
                    ],
                ],
                'timeline' => [
                    [
                        'date' => '2021 — présent',
                        'title' => 'Développeur indépendant et Mentorat',
                        'description' => "Accompagnement technique global, choix d'architecture et développement d'applications web sur mesure pour entreprises et indépendants.",
                    ],
                    [
                        'date' => '2025',
                        'title' => 'Pixli - Remote - Développeur Fullstack',
                        'description' => "Co-développement en équipe d'une plateforme B2B2C avec des pics de fort trafic (gestion et vente de photos scolaires). Création des interfaces frontend avec Nuxt / Vue.js et développement d'endpoints robustes pour l'API backend sous Django (Python).",
                    ],
                    [
                        'date' => '2024',
                        'title' => 'Solecooler - Lead Développeur Fullstack',
                        'description' => 'Conception de A à Z et mise en production de la plateforme e-commerce Solecooler (Stack : Sylius / Symfony / Twig).',
                    ],
                    [
                        'date' => '2021',
                        'title' => 'Ynov - Professeur Développement Web ',
                        'description' => "Enseignement d'un module d'initiation au développement web: premiers pas, bonnes pratiques, git, création portfolio.",
                    ],
                ],
                'education' => [
                    [
                        'date' => '2018 — 2019',
                        'title' => "Bachelor chef de projet informatique - Systèmes d'information et numérique  | Sciences U Lyon ",
                        'description' => 'Spécialisation Gestion de projet Web',
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
                'status' => ArticleStatus::Published,
                'reading_time' => 5,
                'featured' => true,
                'published_at' => now(),
            ]
        );
        $article->categories()->sync([$categories['backend']->id]);

        // Link tags to article
        $article->tags()->sync([
            $tags['Laravel']->id,
            $tags['PHP']->id,
            $tags['Filament']->id,
        ]);

        // 5.5 Extra Articles for other code relationships
        $articleFolder = Article::firstOrCreate(
            ['slug' => 'exploring-laravel-directory-structure'],
            [
                'title' => 'Exploration de la structure des dossiers Laravel',
                'excerpt' => 'Comprendre le rôle de chaque dossier dans l\'arborescence standard d\'un projet Laravel.',
                'content' => 'Le dossier `app` contient le cœur de votre application. Nous allons explorer en détail sa structure interne...',
                'status' => ArticleStatus::Published,
                'reading_time' => 3,
                'featured' => false,
                'published_at' => now(),
            ]
        );
        $articleFolder->categories()->sync([$categories['backend']->id]);

        $articleProject = Article::firstOrCreate(
            ['slug' => 'building-complex-filament-projects'],
            [
                'title' => 'Bâtir des projets complexes avec Filament',
                'excerpt' => 'Comment regrouper ses ressources et personnaliser ses panels d\'administration.',
                'content' => 'Dans ce guide, nous voyons comment modulariser un projet Filament complet en créant des sections dédiées...',
                'status' => ArticleStatus::Published,
                'reading_time' => 8,
                'featured' => false,
                'published_at' => now(),
            ]
        );
        $articleProject->categories()->sync([$categories['backend']->id]);

        // 5.6 Code Projects
        $codeProject = CodeProject::firstOrCreate(
            ['slug' => 'filament-core-project'],
            [
                'name' => 'Filament Core Project',
                'description' => 'Un projet regroupant toute l\'architecture de base de nos panels d\'administration.',
            ]
        );

        // 6. Code Folders
        $appFolder = CodeFolder::firstOrCreate(
            ['path' => 'app'],
            ['name' => 'app', 'sort_order' => 10]
        );
        $appFolder->update(['code_project_id' => $codeProject->id]);

        $providersFolder = CodeFolder::firstOrCreate(
            ['path' => 'app/Providers'],
            ['name' => 'Providers', 'parent_id' => $appFolder->id, 'sort_order' => 20]
        );

        $filamentFolder = CodeFolder::firstOrCreate(
            ['path' => 'app/Providers/Filament'],
            ['name' => 'Filament', 'parent_id' => $providersFolder->id, 'sort_order' => 30]
        );

        // 7. Code Files
        $codeFile = CodeFile::firstOrCreate(
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
                'sort_order' => 10,
            ]
        );

        // 8. Establish new Article -> Code links
        $article->update(['code_file_id' => $codeFile->id]);
        $articleFolder->update(['code_folder_id' => $appFolder->id]);
        $articleProject->update(['code_project_id' => $codeProject->id]);
    }
}
