# 03 — Normes & Conventions de Code

Ce document formalise les conventions de développement, les standards de qualité, de formatage et les règles de sécurité appliquées sur le projet.

---

## 📐 Règles d'Écriture du Code PHP

### 1. Déclaration Strict Types

Toutes les classes PHP doivent commencer par la déclaration de types stricts :

```php
<?php

declare(strict_types=1);
```

### 2. Classes Finales

Toutes les classes qui ne sont pas destinées à être étendues doivent être marquées comme `final`. Cela évite l'héritage sauvage et optimise l'analyse statique :

- **Classes Finales** : Contrôleurs, Resources API, Services, DTOs, Middlewares, Panel Providers, Filament Resources, Modèles Eloquent.
- **Classes non-finales** : Migrations et Seeders uniquement.

### 3. Typage Explicite

- Les paramètres et types de retour de fonctions doivent être explicitement renseignés.
- Les propriétés de classe doivent être typées.
- Éviter le type `mixed` et privilégier l'usage de types stricts, d'Enums ou de types nullable (`?string`).

---

## 🎨 Outils de Formatage et Analyse Statique

Le projet utilise des validateurs de code stricts dont les configurations sont centralisées :

### 1. Laravel Pint (`pint.json`)

Pint formate automatiquement le code en suivant les standards PSR-12 adaptés pour Laravel avec les contraintes suivantes :

- Forcer les types stricts.
- Supprimer les imports inutilisés.
- Rendre les classes finales automatiquement (lorsque applicable).
- Forcer les retours `void` sur les méthodes sans retour.

### 2. PHPStan (`phpstan.neon`)

Analyseur de code statique configuré au **Niveau max** (le niveau d'exigence absolue).

- Tous les chemins de code doivent être exempts d'erreurs de typage.
- Le dossier `app/Filament/` est exclu de l'analyse car Filament génère des liaisons magiques dynamiques incompatibles avec le niveau max de PHPStan.
- Le plugin `phpat/phpat` est activé dans `phpstan.neon` pour faire tourner les tests d'architecture en même temps que l'analyse statique.

```bash
# Lance l'analyse statique ET les tests d'architecture en une seule commande
./vendor/bin/phpstan analyse
```

---

## 🔒 Sécurité et Accès Réseau

### 1. Gestion des Secrets

- Aucun mot de passe, clé d'API, ou token ne doit être écrit en dur dans le code source.
- Tous les secrets doivent être référencés via des variables d'environnement (`env()`) dans des fichiers de configuration, et documentés dans le fichier [.env.example](../.env.example).
- Le fichier `.env` réel ne doit **jamais** être commit sur git.

### 2. Contrôle CORS (`config/cors.php`)

Les requêtes cross-origin vers l'API sont strictement restreintes aux domaines frontend autorisés :

- **Local** : `http://localhost:3000` (configurable via `FRONTEND_URL` dans `.env`).
- **Production** : `https://loicbonin.com` (Portfolio public).
- **Méthodes autorisées** : uniquement `GET`, `HEAD` et `OPTIONS` (l'API est en lecture seule).
- **Cache preflight** : `max_age = 86400` secondes (24 h) — les navigateurs peuvent mettre en cache les requêtes OPTIONS.

### 3. Limiteur de Débit (Rate Limiting)

Toutes les routes de l'API sont protégées contre le déni de service et le spam à l'aide du middleware `throttle:api` configuré à **60 requêtes par minute** par adresse IP dans `AppServiceProvider.php`.

### 4. Filtrage des Projets de Code Non Publiés

L'endpoint `GET /api/v1/code/files/{path}` et `GET /api/v1/code/tree` doivent impérativement filtrer les données appartenant aux projets de code dont `is_published = false`.

- **Règle** : `CodeTreeService::getFileByPath()` doit remonter l'arborescence des dossiers pour vérifier que le projet parent est publié avant de retourner un fichier. Si ce n'est pas le cas, il retourne `null` (→ HTTP 404).
- **Règle** : `CodeTreeService::getFullTree()` doit exclure de l'arborescence tous les dossiers et fichiers appartenant à un projet non publié, y compris leurs sous-dossiers.
- **Raison** : Sans ce filtrage, un attaquant connaissant le chemin d'un fichier pourrait lire le code source d'un projet encore en brouillon via l'API publique (fuite d'information / Information Disclosure).

---

## 📋 Validation des Paramètres d'Entrée (Query Params)

Afin de garantir l'intégrité de l'application et d'éviter les comportements inattendus liés à un typage lâche ou à des injections, toutes les données entrantes via les requêtes HTTP (y compris les paramètres d'URL comme les query params `?category=`, `?page=`, etc.) doivent être validées.

### 1. Form Requests (`app/Http/Requests/`)

- Toute route de contrôleur recevant des données utilisateurs ou des query parameters doit utiliser une classe de requête dédiée (`FormRequest`) héritant de `Illuminate\Foundation\Http\FormRequest`.
- La classe de requête doit être marquée `final class`.
- La méthode `rules()` doit définir explicitement les types attendus (ex: `integer`, `string`, `min`, `max`, `nullable`).
- Les contrôleurs ne doivent **jamais** accéder directement aux paramètres de la requête sans validation préalable. Ils doivent utiliser `$request->validated()` pour récupérer uniquement les données nettoyées et validées.

### 2. Exemple d'implémentation (`ListArticlesRequest.php`)

```php
final class ListArticlesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
```

---

## ⚡ Bonnes Pratiques de Performance et Évitement du N+1

Pour garantir des temps de réponse d'API sous les 50ms et éviter les surcharges de requêtes SQL sur la base de données, les développeurs doivent suivre les règles suivantes :

### 1. Interdiction des requêtes SQL directes et du lazy-loading dans les API Resources
Les transformateurs JSON (`JsonResource`) ne doivent jamais exécuter directement de requêtes SQL ou déclencher du lazy-loading récursif dans leur méthode `toArray()`.
* **Pratique interdite** : Faire `$this->resource->folders()->whereNull(...)->get()` ou naviguer récursivement une relation non-eager-loadée.
* **Pratique recommandée** :
  * Déclarer une relation filtrée sur le modèle (ex : `rootFolders()`).
  * Utiliser `relationLoaded('nomRelation')` pour vérifier si la relation est pré-chargée avant d'appeler les données en base.

### 2. Eager-Loading systématique dans les Services
Toutes les relations affichées ou utilisées dans les API Resources doivent être explicitement déclarées dans le tableau `with([...])` au niveau de la couche Service (ex : `ArticleService::listPublished()`). Les relations à niveaux multiples (ex : `codeFile.folder.parent.parent.codeProject`) doivent être explicitement déclarées jusqu'à la profondeur maximale attendue par la vue.

### 3. Cache statique de cycle de vie de requête (Request-Lifetime Cache)
Lorsqu'un parcours d'arborescence récursif (comme la remontée des parents d'un dossier pour trouver son projet) doit être effectué au cours de la sérialisation d'une collection :
* Déclarer un tableau statique privé sur le modèle faisant office de cache local de requête (ex : `private static array $projectSlugCache = []`).
* Résoudre la valeur récursivement en stockant le résultat dans le tableau statique pour la clé correspondante (ex: `self::$projectSlugCache[$this->id]`).
* Grâce au cycle de vie de PHP-FPM, ce cache est vidé automatiquement à la fin de chaque requête HTTP, éliminant les requêtes redondantes sur un même élément durant la sérialisation sans risque de persistance obsolète entre les requêtes.

---

## 🧪 Stratégie de Tests

L'application applique une stratégie de tests à trois niveaux, chacun vérifiants des contrats différents.

### Niveau 1 — Tests d'Architecture (`tests/Architecture/`)

Vérifiés via `phpat/phpat` à chaque passage de PHPStan. Ces tests garantissent que les règles de découplage des couches sont respectées en permanence.

| Règle | Description |
| :---- | :---------- |
| Modèles → pas de Services ni HTTP | Un Modèle ne peut pas importer un Service ou quoi que ce soit de la couche HTTP. |
| Services → pas de HTTP | Un Service ne peut pas importer un Controller, une Request ou une Resource. |
| Contrôleurs → pas de Modèles | Un Controller ne peut pas importer directement un Modèle Eloquent. |

### Niveau 2 — Tests de Contrat / Snapshot (`tests/Feature/Api/V1/Snapshots/`)

Vérifiés via `spatie/phpunit-snapshot-assertions`. Ces tests verrouillent la **structure exacte** des réponses JSON retournées par l'API. Toute modification involontaire de la forme d'une réponse (renommage de clé, suppression de champ) fait échouer le test avant que le frontend Next.js ne soit impacté.

**Règle concernant les ULIDs dans les snapshots** : Les ULIDs sont générés aléatoirement par `HasUlids`. Pour rendre les snapshots déterministes, tous les tests Snapshot doivent utiliser la méthode utilitaire `assertMatchesJsonSnapshotWithNormalizedIds()` définie dans la classe de test, qui remplace toutes les chaînes ULID par une valeur fixe (`00000000000000000000000000`) avant la comparaison.

```bash
# Régénérer les snapshots après un changement intentionnel de contrat d'API
./vendor/bin/phpunit --filter=Snapshot -d --update-snapshots
```

### Niveau 3 — Tests Unitaires & Feature (`tests/Unit/` & `tests/Feature/`)

* **Tests Unitaires** (`tests/Unit/Services/`) : Chaque service dispose de son propre fichier de tests unitaires validant sa logique métier en isolation (base `RefreshDatabase`).
* **Tests Feature** (`tests/Feature/Api/V1/`) : Tests d'intégration HTTP de bout en bout vérifiant les routes, les codes de statut, les structures de réponse et les règles de sécurité (CORS, rate limiting, accès aux projets non publiés).
