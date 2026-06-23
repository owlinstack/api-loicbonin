<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Profile;

final class ProfileService
{
    /**
     * Récupère les données du profil de développement (avec fallback si vide en base).
     *
     * @return array<string, mixed>
     */
    public function getProfileData(): array
    {
        $profile = Profile::query()->first();

        if ($profile) {
            $showTimeline = $profile->show_timeline ?? true;
            $showEducation = $profile->show_education ?? true;

            return [
                'name' => $profile->name,
                'bio' => $profile->bio,
                'skills' => $profile->skills,
                'showTimeline' => $showTimeline,
                'timeline' => $showTimeline ? $profile->timeline : null,
                'showEducation' => $showEducation,
                'education' => $showEducation ? ($profile->education ?? []) : null,
                'cvUrl' => $profile->cv_url ? asset('storage/'.$profile->cv_url) : '/cv-loic-bonin.pdf',
                'avatarUrl' => $profile->avatar_url ? asset('storage/'.$profile->avatar_url) : null,
            ];
        }

        return $this->getFallbackData();
    }

    /**
     * Données par défaut (fallback) si la table profiles est vide.
     *
     * @return array<string, mixed>
     */
    private function getFallbackData(): array
    {
        return [
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
                    'description' => 'Suivi régulier des évolutions des outils, normes et Framework via source primaire (documentation). Veille active journalière sur l\'utilisation des outils IA.',
                ],
            ],
            'showTimeline' => true,
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
            'showEducation' => true,
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
    }
}
