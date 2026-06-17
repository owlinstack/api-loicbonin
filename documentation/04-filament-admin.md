# 04 — Administration Filament v5

Ce document présente l'architecture du panneau d'administration **AdminCreator**, les configurations sur-mesure appliquées aux ressources de contenu, et le fonctionnement des composants d'édition de code et du profil unique.

---

## 🛡️ Le Panel AdminCreator

L'administration est configurée via [AdminCreatorPanelProvider.php](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/app/Providers/Filament/AdminCreatorPanelProvider.php) et exposée sur l'adresse `/admin-creator`.
* **Couleur du Design System** : Une couleur primaire personnalisée (`#01696f`, Teal) est appliquée pour correspondre à la charte graphique globale.
* **Sécurité** : Connexion requise et gestion MFA prête à être activée en production.
* **Typage strict** : La propriété `$navigationIcon` utilise le typage strict requis par Filament v5 :
  ```php
  protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';
  ```

---

## 📦 Description des Ressources CRUD

### 1. Articles (`ArticleResource`)
* **Éditeur Markdown ergonomique** : Le champ `content` utilise le composant `MarkdownEditor` configuré à une hauteur confortable de `400px`, avec une barre d'outils complète incluant le mode plein écran, l'aperçu côte à côte, et l'insertion de fichiers médias.
* **Création de Catégorie en Ligne** : Le sélecteur `category_id` intègre un bouton `+` (`createOptionForm`) permettant de créer une catégorie à la volée dans une fenêtre modale sans perdre la saisie de l'article.

### 2. Catégories (`CategoryResource`)
* Permet de créer, éditer et trier les catégories.
* Affiche dans sa table principale un badge dynamique contenant le décompte d'articles associés (`articles_count`).

### 3. Projets (`ProjectResource`)
* Administre le portfolio de réalisations.
* Utilise un composant `TagsInput` pour manipuler le tableau JSON `tech_stack` de manière visuelle et intuitive.

### 4. Code & Arborescences (`CodeFolderResource` et `CodeFileResource`)
Permet de configurer l'explorateur de fichiers interactif du frontend Next.js :
* **Dossiers (`CodeFolderResource`)** : Un script d'aide (`afterStateUpdated`) calcule automatiquement le chemin logique (`path`) du dossier en se basant sur le dossier parent sélectionné (ex : si `parent` est `app` et `name` est `Http`, le chemin devient `app/Http`).
* **Fichiers (`CodeFileResource`)** : Pour l'édition de code brut, le textarea standard a été transformé en éditeur de code monospace léger et ergonomique grâce à des attributs HTML personnalisés (`font-mono`, arrière-plan sombre, interlignage aéré).

---

## 👤 La Page Profil Unique (Settings UX)

Pour éviter l'affichage d'un tableau de liste inadapté à un profil personnel unique, l'édition du profil s'effectue via une **Page de Paramètres** dédiée : [ManageProfile.php](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/app/Filament/Pages/ManageProfile.php).

* **Concept** : La page charge le premier enregistrement de la table `profiles` (ou le crée s'il est manquant) lors de sa phase de montage (`mount`).
* **Repeaters interactifs** :
  * Un composant `Repeater` permet de gérer de manière fluide et par glisser-déposer les compétences complexes (`skills`).
  * Un autre `Repeater` gère la timeline professionnelle (`timeline`) comprenant les dates, intitulés de postes, et descriptions de tâches.
* **Chargement de CV PDF** : Un composant `FileUpload` restreint au type `application/pdf` permet de charger le fichier CV directement vers le dossier `storage/cvs/`.
* **Intégration Blade** : Le rendu s'effectue dans [manage-profile.blade.php](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/resources/views/filament/pages/manage-profile.blade.php) à l'aide des structures et boutons natifs de Filament.
