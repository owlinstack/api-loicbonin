<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProfileResource;
use App\Models\Profile;

final class ProfileController extends Controller
{
    public function show(): ProfileResource
    {
        $profile = Profile::first();

        $data = $profile ? [
            'name' => $profile->name,
            'bio' => $profile->bio,
            'skills' => $profile->skills,
            'timeline' => $profile->timeline,
            'education' => $profile->education ?? [],
            'cvUrl' => $profile->cv_url ? asset('storage/'.$profile->cv_url) : '/cv-loic-bonin.pdf',
            'avatarUrl' => $profile->avatar_url ? asset('storage/'.$profile->avatar_url) : null,
        ] : [
            'name' => 'Loïc Bonin',
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
                    'description' => 'Suivi régulier des évolutions des outils, normes et Framework via source primaire (documentation). Veille active journalière sur l\'évolution et l\'utilisation des outils IA.',
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
            'cvUrl' => '/cv-loic-bonin.pdf',
            'avatarUrl' => null,
        ];

        return new ProfileResource($data);
    }
}
