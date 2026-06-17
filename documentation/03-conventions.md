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
- **Classes Finales** : Contrôleurs, Resources API, Services, DTOs, Middlewares, Panel Providers, Filament Resources.
- **Classes non-finales** : Modèles Eloquent (requis par le proxying d'Eloquent), Migrations et Seeders.

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
Analyseur de code statique configuré au **Niveau 8** (le niveau le plus strict recommandable en Laravel).
- Tous les chemins de code doivent être exempts d'erreurs de typage.
- Le dossier `app/Filament/` est exclu de l'analyse car Filament génère des liaisons magiques dynamiques incompatibles avec le niveau 8 de PHPStan.

---

## 🔒 Sécurité et Accès Réseau

### 1. Gestion des Secrets
- Aucun mot de passe, clé d'API, ou token ne doit être écrit en dur dans le code source.
- Tous les secrets doivent être référencés via des variables d'environnement (`env()`) dans des fichiers de configuration, et documentés dans le fichier [env.example](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/.env.example).
- Le fichier `.env` réel ne doit **jamais** être commit sur git.

### 2. Contrôle CORS (`config/cors.php`)
Les requêtes cross-origin vers l'API sont strictement restreintes aux domaines frontend autorisés :
- **Local** : `http://localhost:3000` (Développement Next.js).
- **Production** : `https://loicbonin.com` (Portfolio public).

### 3. Limiteur de Débit (Rate Limiting)
Toutes les routes de l'API sont protégées contre le déni de service et le spam à l'aide du middleware `throttle:api` configuré à **60 requêtes par minute** par adresse IP dans `AppServiceProvider.php`.
