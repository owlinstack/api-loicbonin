<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Tests d'architecture pour garantir l'indépendance et le découplage des couches de l'application.
 * Les Modèles ne doivent pas dépendre des Services ou de la couche HTTP.
 * Les Services ne doivent pas dépendre de la couche HTTP.
 * Les Contrôleurs ne doivent pas dépendre directement des Modèles (utilisation obligatoire des Services).
 */
final class ArchitectureTest
{
    /**
     * Les Modèles ne doivent pas dépendre des Services ou de la couche HTTP (Controllers/Requests/Resources).
     */
    public function test_models_do_not_depend_on_services_or_http(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Models'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace('App\Services'),
                Selector::inNamespace('App\Http')
            );
    }

    /**
     * Les Services ne doivent pas dépendre de la couche HTTP (Controllers/Requests/Resources).
     * Les Services représentent la logique métier et doivent être isolés du transport HTTP.
     */
    public function test_services_do_not_depend_on_http(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Services'))
            ->shouldNot()
            ->dependOn()
            ->classes(Selector::inNamespace('App\Http'));
    }

    /**
     * Les Contrôleurs API ne doivent pas dépendre directement des Modèles Eloquent.
     * Ils doivent obligatoirement passer par les Services métiers.
     */
    public function test_controllers_do_not_depend_on_models(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Http\Controllers\Api\V1'))
            ->shouldNot()
            ->dependOn()
            ->classes(Selector::inNamespace('App\Models'));
    }
}
