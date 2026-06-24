<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO immuable et strictement typé unifiant le contrat de données du profil.
 * Justification : Permet de typer de façon identique les données venant de la base ou du fallback statique,
 * tout en découplant complètement la couche de persistance/métier de la couche de présentation (HTTP).
 */
final readonly class ProfileData
{
    /**
     * @param  array<int, array{term: string, description: string}>  $skills
     * @param  array<int, array{date: string, title: string, description: string}>|null  $timeline
     * @param  array<int, array{date: string, title: string, description: string}>|null  $education
     */
    public function __construct(
        public string $name,
        public string $bio,
        public array $skills,
        public bool $showTimeline,
        public ?array $timeline,
        public bool $showEducation,
        public ?array $education,
        public ?string $cvPath,
        public ?string $avatarPath,
    ) {
        //
    }
}
