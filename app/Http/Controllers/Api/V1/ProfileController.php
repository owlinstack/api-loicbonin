<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProfileResource;
use App\Services\ProfileService;

/**
 * Contrôleur API pour gérer le profil utilisateur / de développement.
 * Justification : Expose les informations professionnelles de présentation,
 * en s'appuyant sur ProfileService pour l'hydratation (base ou fallback statique).
 */
final class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {
        //
    }

    /**
     * Retourne les données complètes du profil.
     * Choix : Utilise le DTO immuable ProfileData au travers de la ressource ProfileResource pour un contrat de données strict.
     */
    public function show(): ProfileResource
    {
        return new ProfileResource($this->profileService->getProfileData());
    }
}
