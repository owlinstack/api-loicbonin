# 02 — Architecture & Découpage Applicatif

Ce document détaille la structure de répertoires du backend Laravel 13, les responsabilités de chaque couche logicielle et le flux de données lors du traitement d'une requête API.

---

## 📂 Organisation du Code Source

L'application suit l'architecture standard de Laravel enrichie d'une couche de services métier et de DTOs pour structurer les échanges.

```
api-loicbonin/app/
├── DTOs/                # Objets de transfert de données (lecture seule, immuables)
│   ├── ArticleData.php
│   └── ...
├── Enums/               # Enums PHP natifs typés string (ex: ArticleStatus)
│   ├── ArticleStatus.php
│   └── ...
├── Filament/            # Configuration de l'administration Filament v5
│   ├── Pages/           # Pages personnalisées (ex: ManageProfile)
│   ├── Resources/       # Définition des CRUD de ressources (Articles, Projets...)
│   └── Providers/       # Fournisseur du panel AdminCreatorPanelProvider
├── Http/
│   ├── Controllers/     # Contrôleurs API versionnés (GET/POST...)
│   │   └── Api/V1/
│   └── Resources/       # API Resources de transformation JSON (V1)
│       └── V1/
│           ├── CodeFolderResource.php
│           ├── CodeProjectResource.php
│           └── ...
├── Models/              # Modèles Eloquent avec ULIDs comme clés primaires
│   ├── Article.php
│   ├── Category.php
│   ├── CodeProject.php
│   ├── Profile.php
│   └── ...
└── Services/            # Logique métier pure (lecture, écriture, calculs)
    └── ArticleService.php
```

---

## 🔄 Flux de Données Typique (API REST)

Lorsqu'un visiteur charge une page sur le frontend (ex : la liste des articles), la requête traverse les couches suivantes :

```
[ Frontend Next.js ]
        │  (Requête HTTP GET /api/v1/articles)
        ▼
[ HTTP Routing ]  ──► Applique le middleware de limitation de débit (throttle:api)
        │
        ▼
[ ArticleController ] ──► Extrait et convertit les paramètres de requête (?category=)
        │
        ▼
[ ArticleService ] ──► Exécute la logique (sélectionne les articles publiés, filtre, trie)
        │
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

### 1. Modèles Eloquent (`app/Models/`)
* **Identifiants** : Tous les modèles (sauf pivot) utilisent le trait `HasUlids` pour générer des identifiants uniques temporels (ULIDs), plus performants et ordonnables par rapport aux UUIDs.
* **Relations** : Les relations sont strictement typées (ex: `belongsTo`, `hasMany`).
* **Casts** : Les types complexes comme le JSON (compétences, timeline, stack technique) sont castés en tableaux PHP (`array`) pour une manipulation simplifiée.

### 2. Couche de Service (`app/Services/`)
Toute la logique de requêtage, de filtre et de calcul est centralisée dans les services (ex : `ArticleService`). Les contrôleurs ne contiennent aucune requête SQL. 

### 3. Contrôleurs API (`app/Http/Controllers/Api/V1/`)
Les contrôleurs valident les paramètres d'entrée, appellent le service approprié et retournent une ressource API. Ils sont versionnés sous `V1/` pour anticiper les évolutions futures.

### 4. API Resources (`app/Http/Resources/V1/`)
Ils jouent le rôle de traducteurs entre le monde PHP/SQL (snake_case) et le monde JavaScript/TypeScript (camelCase) :
* Conversion de `published_at` en `publishedAt`.
* Conversion des relations en structures simplifiées (ex: renvoyer le slug de la catégorie au lieu de l'objet catégorie complet).
* Suppression de l'enveloppement automatique (`public static $wrap = null` et surcharge de `toResponse`) pour livrer des JSON plats conformes aux schémas Zod.

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
    "skills": [
      { "term": "Frontend", "description": "Vue.JS, Nuxt.JS..." }
    ],
    "timeline": [
      { "date": "2021 — présent", "title": "Développeur indépendant", "description": "..." }
    ],
    "education": [
      { "date": "2018 — 2019", "title": "Bachelor Sciences U", "description": "..." }
    ],
    "cvUrl": "http://localhost:8000/storage/cvs/cv.pdf",
    "avatarUrl": "http://localhost:8000/storage/avatars/avatar.jpg"
  }
  ```

### 2. Articles (`GET /api/v1/articles` & `GET /api/v1/articles/{slug}`)
* **Liste** (`GET /api/v1/articles?category={slug}&tag={name}&page={number}`) : Renvoie les articles publiés paginés.
* **Détails** (`GET /api/v1/articles/{slug}`) : Renvoie un article unique avec ses liaisons de code source éventuelles (`codeFile`, `codeFolder`, ou `codeProject`).

### 3. Projets Portfolio (`GET /api/v1/projects` & `GET /api/v1/projects/{slug}`)
Renvoie la liste ou le détail des réalisations du portfolio (champs `title`, `description`, `techStack`, `liveUrl`, `repoUrl`).

### 4. Catégories & Tags (`GET /api/v1/categories` & `GET /api/v1/tags`)
* `/categories` : Retourne la liste des catégories avec le décompte d'articles associés (`count`).
* `/tags` : Retourne la liste à plat des tags uniques sous forme de tableau de chaînes.

### 5. Explorateur de Code Source
* `GET /api/v1/code/projects` : Retourne la liste à plat des projets de code disponibles (id, name, slug, description).
* `GET /api/v1/code/projects/{slug}/tree` : Retourne l'arborescence récursive complète des dossiers et fichiers pour le projet spécifié.
* `GET /api/v1/code/files/{path}` : Retourne le détail d'un fichier source unique (nom, langage, contenu de code brut) identifié par son chemin (ex : `app/Providers/Filament/AdminCreatorPanelProvider.php`).
* `GET /api/v1/code/tree` (Legacy) : Retourne l'arborescence globale des dossiers et fichiers de code à la racine.

