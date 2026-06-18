# 04 — Administration Filament v5

Ce document présente l'architecture du panneau d'administration **AdminCreator**, les configurations sur-mesure appliquées aux ressources de contenu, et le fonctionnement des composants d'édition de code et du profil unique.

---

## 🛡️ Le Panel AdminCreator

L'administration est configurée via [AdminCreatorPanelProvider.php](../app/Providers/Filament/AdminCreatorPanelProvider.php) et exposée sur l'adresse `/admin-creator`.

- **Couleur du Design System** : Une couleur primaire personnalisée (`#01696f`, Teal) est appliquée pour correspondre à la charte graphique globale.
- **Sécurité** : Connexion requise et gestion MFA prête à être activée en production.
- **Typage strict** : La propriété `$navigationIcon` utilise le typage strict requis par Filament v5 :
    ```php
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';
    ```

---

## 📦 Description des Ressources CRUD

### 1. Articles (`ArticleResource`)

- **Mise en page mono-colonne pleine largeur** : Le formulaire est structuré de manière entièrement séquentielle sur une seule colonne. Toutes les sections (`Contenu`, `Métadonnées`, `Code Source Associé`) et tous les champs internes s'affichent les uns en dessous des autres en occupant toute la largeur disponible pour maximiser l'espace d'édition.
  - **Section "Contenu"** (avec icône `heroicon-o-document-text`) : Regroupe le titre, le slug, le résumé et l'éditeur markdown principal (`content`) configuré à une hauteur confortable de `400px`. Cet éditeur prend en charge le téléversement d'images (bouton d'attachement ou glisser-déposer), configuré pour enregistrer les fichiers sur le disque `public` (répertoire `attachments/`).
  - **Section "Métadonnées"** (avec icône `heroicon-o-tag`) : Regroupe la catégorie, les tags multiples, le statut, le temps de lecture estimé, le commutateur de mise en avant et la date de publication.
  - **Section "Code Source Associé"** (avec icône `heroicon-o-code-bracket`) : Regroupe le sélecteur réactif du type de liaison et les sélecteurs dynamiques de code (fichier, dossier, projet).
- **Création en Ligne** : Permet de créer des catégories et des tags à la volée directement depuis les fenêtres modales intégrées aux sélecteurs sans interrompre la saisie.

### 2. Catégories (`CategoryResource`)

- Permet de créer, éditer et trier les catégories.
- Affiche dans sa table principale un badge dynamique contenant le décompte d'articles associés (`articles_count`).

### 3. Projets (`ProjectResource`)

- Administre le portfolio de réalisations.
- Utilise un composant `TagsInput` pour manipuler le tableau JSON `tech_stack` de manière visuelle et intuitive.

### 4. Projets, Dossiers & Fichiers de Code (`CodeProjectResource`, `CodeFolderResource` et `CodeFileResource`)

Permet de configurer l'explorateur de fichiers interactif et les projets de code associés aux articles :

- **Projets Code (`CodeProjectResource`)** : Gère les regroupements de dossiers sous un projet de code unique (ex : Filament Core Project).
- **Dossiers (`CodeFolderResource`)** : Permet de créer l'arborescence des dossiers et d'y lier un projet de code parent. Un script d'aide (`afterStateUpdated`) calcule automatiquement le chemin logique (`path`) du dossier en se basant sur le dossier parent sélectionné (ex : si `parent` est `app` et `name` est `Http`, le chemin devient `app/Http`).
- **Fichiers (`CodeFileResource`)** : Éditeur de code brut configuré avec une typographie monospace sombre. La liaison avec un article a été retirée pour être pilotée directement et de manière centralisée par la fiche de l'Article.

---

## 👤 La Page Profil Unique (Settings UX)

Pour éviter l'affichage d'un tableau de liste inadapté à un profil personnel unique, l'édition du profil s'effectue via une **Page de Paramètres** dédiée : [ManageProfile.php](../app/Filament/Pages/ManageProfile.php).

- **Concept** : La page charge le premier enregistrement de la table `profiles` (ou le crée s'il est manquant) lors de sa phase de montage (`mount`).
- **Repeaters interactifs** :
    - Un composant `Repeater` permet de gérer de manière fluide et par glisser-déposer les compétences complexes (`skills`).
    - Un composant `Repeater` gère la timeline professionnelle (`timeline`) comprenant les dates, intitulés de postes, et descriptions de tâches.
    - Un composant `Repeater` gère le parcours académique et les formations (`education`) comprenant les dates, intitulés de diplômes, et descriptions.
- **Téléversement de Médias** :
    - **Photo de Profil (Avatar)** : Un composant `FileUpload` d'image (`image()`) stockant la photo de profil sous le dossier `storage/avatars/`.
    - **CV PDF** : Un composant `FileUpload` restreint au type `application/pdf` stockant le fichier CV sous `storage/cvs/`.
    - _Note_ : Les deux champs utilisent `preserveFilenames()` pour conserver le nom d'origine des fichiers.
- **Intégration Blade** : Le rendu s'effectue dans [manage-profile.blade.php](../resources/views/filament/pages/manage-profile.blade.php) à l'aide des structures et boutons natifs de Filament.
