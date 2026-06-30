# 02 — Architecture & Découpage Applicatif

Ce document détaille la structure de répertoires du backend Laravel 13, les responsabilités de chaque couche logicielle et le flux de données lors du traitement d'une requête API.

---

## 📂 Organisation du Code Source

L'application suit une architecture en couches strictes (Layered Architecture) garantissant le découplage entre le transport HTTP, la logique métier et la persistance des données.

```
api-loicbonin/app/
├── DTOs/                  # Objets de Transfert de Données immuables
│   └── ProfileData.php    # DTO du profil (readonly, pas de logique métier)
├── Enums/                 # Enums PHP natifs typés string
│   └── ArticleStatus.php  # Statut d'un article (Draft, Published, Archived)
├── Filament/              # Configuration de l'administration Filament v5
│   ├── Pages/             # Pages personnalisées (ex: ManageProfile)
│   └── Resources/         # Définition des CRUD de ressources (Articles, Projets...)
├── Http/
│   ├── Controllers/       # Contrôleurs API versionnés — orchestration uniquement
│   │   └── Api/V1/
│   │       ├── ArticleController.php
│   │       ├── CategoryController.php
│   │       ├── CodeController.php
│   │       ├── ProfileController.php
│   │       ├── ProjectController.php
│   │       └── TagController.php
│   ├── Requests/          # Form Requests — validation & typage des entrées HTTP
│   │   └── V1/
│   │       └── ListArticlesRequest.php
│   └── Resources/         # API Resources — transformation JSON (snake_case → camelCase)
│       └── V1/
│           ├── ArticleCollection.php
│           ├── ArticleResource.php
│           ├── CategoryResource.php
│           ├── CodeFileResource.php
│           ├── ProfileResource.php
│           ├── ProjectResource.php
│           └── TagResource.php
├── Models/                # Modèles Eloquent avec ULIDs comme clés primaires
│   ├── Article.php
│   ├── Category.php
│   ├── CodeFile.php
│   ├── CodeFolder.php
│   ├── CodeProject.php
│   ├── Profile.php
│   ├── Project.php
│   ├── Tag.php
│   └── User.php
├── Providers/             # Fournisseurs de services
│   ├── AppServiceProvider.php
│   └── Filament/          # Fournisseur du panel AdminCreatorPanelProvider
└── Services/              # Logique métier pure — la seule couche qui touche Eloquent
    ├── ArticleService.php     # Articles publiés, filtres, pagination
    ├── CategoryService.php    # Catégories avec décompte d'articles publiés
    ├── CodeTreeService.php    # Arborescence de code, filtrage projets publiés
    ├── ProfileService.php     # Profil (DB ou fallback statique)
    ├── ProjectService.php     # Projets portfolio
    └── TagService.php         # Tags
```

```
api-loicbonin/tests/
├── Architecture/              # Tests d'architecture (phpat/phpat via PHPStan)
│   └── ArchitectureTest.php   # Règles de découplage des couches
├── Feature/
│   └── Api/V1/
│       ├── ArticleApiTest.php     # Tests d'intégration HTTP
│       ├── GeneralApiTest.php     # Tests d'intégration HTTP (divers + sécurité)
│       ├── ProjectApiTest.php     # Tests d'intégration HTTP
│       ├── SecurityAndCorsTest.php # CORS, Rate Limiting, sécurité réseau
│       └── Snapshots/             # Tests de contrat (spatie/phpunit-snapshot-assertions)
│           ├── ArticleSnapshotTest.php
│           ├── ProfileSnapshotTest.php
│           └── ProjectSnapshotTest.php
└── Unit/
    ├── Models/
    │   └── CodeFolderTest.php     # Test unitaire de la logique du modèle
    └── Services/
        ├── ArticleServiceTest.php
        ├── CategoryServiceTest.php
        ├── CodeTreeServiceTest.php
        ├── ProfileServiceTest.php
        ├── ProjectServiceTest.php
        └── TagServiceTest.php
```

---

## 🔄 Flux de Données Typique (API REST)

Lorsqu'un visiteur charge une page sur le frontend (ex : la liste des articles), la requête traverse les couches suivantes :

```
[ Frontend Next.js ]
        │  (Requête HTTP GET /api/v1/articles?category=react&page=1)
        ▼
[ HTTP Routing ]  ──► Applique le middleware de limitation de débit (throttle:api)
        │
        ▼
[ Form Request ]  ──► Valide, type et caste les paramètres d'entrée (ListArticlesRequest)
        │              ex: ?page=1 (string) est casté en int pour le service
        ▼
[ ArticleController ] ──► Reçoit les données validées et appelle le service métier
        │                  Ne contient aucune logique SQL ni logique métier
        ▼
[ ArticleService ] ──► Exécute la logique métier (filtres, tri, eager-loading)
        │               Seule couche autorisée à interagir avec Eloquent
        ▼
[ Eloquent Models ] ──► Requête SQLite/PostgreSQL (liaisons de tables, pagination)
        │
        ▼
[ ArticleCollection ] ──► Formate à plat la réponse JSON sans enveloppe (sans "data")
        │
        ▼
[ ArticleResource ] ──► Convertit les clés snake_case de base en camelCase (contrat TS)
        │
        ▼
[ Frontend Next.js ] ──► Reçoit le JSON plat et le valide avec le schéma Zod
```

---

## 🧩 Responsabilités des Couches

### 1. Enums (`app/Enums/`)
Les Enums PHP natifs (backed enums) remplacent les constantes magiques et les colonnes string non typées. Ils garantissent qu'une valeur ne peut prendre que des états connus et explicites (ex: `ArticleStatus::Published`). Leurs cas sont directement utilisables par Eloquent via le système de `$casts`.

### 2. DTOs (`app/DTOs/`)
Les Data Transfer Objects sont des classes PHP `readonly` immuables. Ils structurent les données entre la couche Service et la couche HTTP sans permettre de mutation accidentelle. Exemple : `ProfileData` transporte les données du profil de `ProfileService` vers `ProfileResource`, garantissant un contrat de données explicite.

### 3. Modèles Eloquent (`app/Models/`)
* **Identifiants** : Tous les modèles (sauf pivot) utilisent le trait `HasUlids` pour générer des identifiants uniques temporels (ULIDs), plus performants et ordonnables par rapport aux UUIDs.
* **Relations** : Les relations sont strictement typées (ex: `belongsTo`, `hasMany`).
* **Casts** : Les types complexes comme le JSON (compétences, timeline, stack technique) sont castés en tableaux PHP (`array`) ou en Enums pour une manipulation simplifiée et typée.
* **Règle d'or** : Les Modèles ne contiennent aucune logique métier applicative. Seule la logique d'accès aux données propre au modèle est tolérée (ex: cache statique `getProjectSlug()` dans `CodeFolder`).

### 4. Couche de Service (`app/Services/`)
**La couche la plus importante de l'application.** Toute la logique de requêtage, de filtre, de calcul et d'agrégation est centralisée dans les services. Les contrôleurs ne contiennent aucune requête SQL ni aucun appel Eloquent direct.

| Service             | Responsabilité principale                                                               |
| :------------------ | :-------------------------------------------------------------------------------------- |
| `ArticleService`    | Articles publiés, filtres catégorie/tag, pagination, eager-loading des relations        |
| `CategoryService`   | Catégories avec comptage des articles publiés associés (évite le N+1)                  |
| `CodeTreeService`   | Arborescence récursive des projets de code, filtrage des projets non publiés (sécurité) |
| `ProfileService`    | Chargement du profil depuis la DB avec fallback statique intégré                        |
| `ProjectService`    | Projets portfolio, tri par ordre de préférence puis par date                            |
| `TagService`        | Liste plate des noms de tags uniques                                                    |

### 5. Form Requests (`app/Http/Requests/V1/`)
Les requêtes entrantes complexes ou nécessitant une validation stricte (ex: query params de filtrage ou pagination) sont validées et typées via des Form Requests dédiées avant d'atteindre les contrôleurs. Elles garantissent un typage fort pour la couche service et castent les types quand nécessaire (ex: le `?page=1` de l'URL, reçu comme `string`, est casté en `int` dans `validated()`).

### 6. Contrôleurs API (`app/Http/Controllers/Api/V1/`)
Les contrôleurs reçoivent les données déjà validées par les Form Requests, orchestrent l'appel aux services métier correspondants, et retournent la ressource API formatée. Ils sont versionnés sous `V1/`. **Ils ne contiennent ni logique SQL, ni logique métier.**

### 7. API Resources (`app/Http/Resources/V1/`)
Ils jouent le rôle de traducteurs entre le monde PHP/SQL (snake_case) et le monde JavaScript/TypeScript (camelCase) :
* Conversion de `published_at` en `publishedAt`.
* Conversion des relations en structures simplifiées (ex: renvoyer le slug de la catégorie au lieu de l'objet catégorie complet).
* Suppression de l'enveloppement automatique (`public static $wrap = null` et surcharge de `toResponse`) pour livrer des JSON plats conformes aux schémas Zod.

---

## 🏛️ Tests d'Architecture (phpat/phpat)

Les règles de découplage entre les couches sont vérifiées automatiquement à chaque analyse PHPStan via `phpat/phpat` :

| Règle | Signification |
| :---- | :------------ |
| **Modèles → pas de Services ni HTTP** | Un modèle Eloquent ne peut pas importer un Service ou un Controller. |
| **Services → pas de HTTP** | Un Service ne peut pas importer un Controller, une Request ou une Resource. |
| **Contrôleurs → pas de Modèles** | Un Controller ne peut pas importer directement un Modèle Eloquent. |

```bash
# Vérifier les règles d'architecture (0 erreur = règles respectées)
./vendor/bin/phpstan analyse
```

---

## 🔐 Sécurité — Filtrage des Projets Non Publiés

`CodeTreeService` applique deux protections contre la fuite d'informations (Information Disclosure) :

1. **`getFullTree()`** : Filtre en mémoire les dossiers et fichiers appartenant à des projets dont `is_published = false`. La propagation est récursive pour couvrir les sous-dossiers.
2. **`getFileByPath()`** : Remonte l'arborescence des dossiers jusqu'au projet parent et vérifie que `is_published = true` avant de retourner le fichier. Retourne `null` (→ HTTP 404) sinon.

---

## 📡 Points de Terminaison API (Endpoints V1)

Toutes les routes de l'API sont préfixées par `/api/v1/` et sont en lecture seule (stateless).

### 1. Profil (`GET /api/v1/profile`)
Retourne les données d'identité, les compétences, le parcours professionnel (timeline) et académique (éducation) du développeur.
* **Payload de Réponse (JSON)** :
  ```json
  {
    "name": "Loïc Bonin",
    "bio": "Développeur full-stack basé à Lyon...",
    "showTimeline": true,
    "skills": [
      { "term": "Frontend", "description": "Vue.JS, Nuxt.JS..." }
    ],
    "timeline": [
      { "date": "2021 — présent", "title": "Développeur indépendant", "description": "..." }
    ],
    "showEducation": true,
    "education": [
      { "date": "2018 — 2019", "title": "Bachelor Sciences U", "description": "..." }
    ],
    "cvUrl": "http://localhost:8000/storage/cvs/cv.pdf",
    "avatarUrl": "http://localhost:8000/storage/avatars/avatar.jpg"
  }
  ```

### 2. Articles (`GET /api/v1/articles` & `GET /api/v1/articles/{slug}`)
* **Liste** (`GET /api/v1/articles?category={slug}&tag={name}&page={number}&pageSize={number}`) : Renvoie les articles publiés paginés. Paramètres validés et castés par `ListArticlesRequest`.
* **Détails** (`GET /api/v1/articles/{slug}`) : Renvoie un article unique avec ses liaisons de code source éventuelles (`codeFile`, `codeFolder`, ou `codeProject`).

### 3. Projets Portfolio (`GET /api/v1/projects` & `GET /api/v1/projects/{slug}`)
Renvoie la liste ou le détail des réalisations du portfolio (champs `title`, `description`, `techStack`, `liveUrl`, `repoUrl`, `year`).

### 4. Catégories & Tags (`GET /api/v1/categories` & `GET /api/v1/tags`)
* `/categories` : Retourne la liste des catégories avec le décompte d'articles publiés associés (`count`) via la relation plusieurs-à-plusieurs pivot. Le comptage exclut les brouillons et les articles archivés.
* `/tags` : Retourne la liste à plat des tags uniques sous forme de tableau de chaînes.

### 5. Explorateur de Code Source
> ⚠️ Tous les endpoints de code filtrent automatiquement les projets dont `is_published = false`.

* `GET /api/v1/code/projects` : Retourne la liste à plat des projets de code **publiés** (id, name, slug, description).
* `GET /api/v1/code/projects/{slug}/tree` : Retourne l'arborescence récursive complète des dossiers et fichiers pour le projet spécifié (404 si non publié).
* `GET /api/v1/code/files/{path}` : Retourne le détail d'un fichier source unique (nom, langage, contenu de code brut) identifié par son chemin (ex : `app/Services/ArticleService.php`). Retourne 404 si le fichier appartient à un projet non publié.
* `GET /api/v1/code/tree` : Retourne l'arborescence globale des dossiers et fichiers de code, en excluant les projets non publiés.
