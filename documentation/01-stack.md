# 01 — Stack Technique du Backend

Ce document liste la pile technologique (stack) du backend, les versions majeures retenues, et justifie les choix afin de garantir un service API performant, sécurisé et pérenne.

---

## 🛠️ Composants de la Stack

| Technologie      | Version   | Rôle dans le projet                           | Justification technique                                                                                                                           |
| :--------------- | :-------- | :-------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------ |
| **PHP**          | `8.4.x`   | Langage de programmation principal.           | Typage de propriétés strict, support des Enums natifs, closures courtes, et constantes typées. Requis pour Laravel 13.                           |
| **Laravel**      | `13.x`    | Framework applicatif principal (API REST).    | Rapidité de mise en place de routes, contrôleurs versionnés, transformations JSON (API Resources) et support du rate limiting natif.             |
| **Filament**     | `v5.x`    | Panneau d'administration (AdminCreator).       | Génération d'interfaces d'administration asynchrones basées sur Livewire v4, utilisation de structures `Schema` découplées et MFA intégré.        |
| **SQLite**       | `3.x`     | Base de données de développement & tests.     | Légèreté totale en local (fichier `database.sqlite` ou base `:memory:` pour les tests), évitant la dépendance d'un service de base externe.      |
| **PHPStan**      | `^2.x`    | Analyseur statique (avec Larastan).           | Garantie de la sécurité des types au niveau 8, réduction des erreurs d'exécution en vérifiant les chemins d'exécution et méthodes.                |
| **Laravel Pint** | `^1.x`    | Formateur de code automatique.                | Uniformisation du style de programmation (PSR-12 amélioré avec typage strict forcé, méthodes finales et retours void).                             |

---

## 💡 Principes de Conception Technique

### 1. Philosophie "YAGNI" (You Aren't Gonna Need It)
Afin de limiter la taille de la base de code, nous appliquons une logique de minimalisme :
- **Pas de package superflu** : Pas d'installation d'éditeurs de texte tiers lourds alors que les composants natifs de Filament (`MarkdownEditor` et `Textarea` monospace stylisé) répondent à 100% du besoin avec du CSS léger.
- **SQLite en Local** : Utilisation de SQLite pour les développements locaux afin de réduire le temps de configuration d'un environnement de développement.

### 2. Typage Strict
Toutes les classes PHP de l'application commencent obligatoirement par la déclaration de type strict :
```php
declare(strict_types=1);
```
Chaque signature de méthode (paramètres et retour) doit être typée. Le type `mixed` ou l'absence de retour est évité au maximum pour garantir la fiabilité de l'analyse statique.

### 3. API REST Stateless
Le backend sert exclusivement d'API REST stateless au frontend Next.js :
- L'authentification utilise un limiteur de débit (`throttle:api`) pour bloquer les requêtes abusives.
- Les données de l'API sont servies sous forme de collections plates sans enveloppe de données inutile (surcharge de `toResponse` sur les Resource Collections) pour que le frontend Next.js puisse valider directement avec des schémas Zod à plat.
