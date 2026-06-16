<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProfileResource;

class ProfileController extends Controller
{
    public function show(): ProfileResource
    {
        $profile = \App\Models\Profile::first();

        $data = $profile ? [
            'name' => $profile->name,
            'bio' => $profile->bio,
            'skills' => $profile->skills,
            'timeline' => $profile->timeline,
            'cvUrl' => $profile->cv_url ? asset('storage/' . $profile->cv_url) : '/cv-loic-bonin.pdf',
        ] : [
            'name' => 'Loïc Bonin',
            'bio' => "Développeur full-stack basé à Paris. Je construis des interfaces de lecture, des outils pour équipes éditoriales et des systèmes de synchronisation de données. Passionné par la typographie, les standards du web et les DX raisonnables.",
            'skills' => [
                [
                    'term' => 'Frontend',
                    'description' => "React, Next.js, TypeScript strict, CSS moderne (Container Queries, View Transitions, Layers). Sensible à la performance perçue et à l'accessibilité.",
                ],
                [
                    'term' => 'Backend',
                    'description' => "Node.js, Drizzle ORM, PostgreSQL (Neon), Cloudflare Workers. Architecture orientée data pipelines plutôt que microservices.",
                ],
                [
                    'term' => 'Outillage',
                    'description' => "Vitest, Playwright, Turborepo, GitHub Actions. Préférence pour des chaînes simples et compréhensibles plutôt qu'exhaustives.",
                ],
                [
                    'term' => 'Veille',
                    'description' => "Suivi régulier des RFC TC39, WICG et CSSWG. Lecture de spécifications en source primaire plutôt que de résumés.",
                ]
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
                    'description' => "Refonte du design system, migration vers App Router, réduction de 40% du bundle JS.",
                ],
                [
                    'date' => '2019 — 2022',
                    'title' => 'Développeur Full-stack, Alan',
                    'description' => "Interfaces patient et back-office médical. First employee engineering.",
                ],
                [
                    'date' => '2017 — 2019',
                    'title' => 'Master Informatique, EPITA',
                    'description' => "Spécialisation systèmes et réseaux.",
                ]
            ],
            'cvUrl' => '/cv-loic-bonin.pdf',
        ];

        return new ProfileResource($data);
    }
}
