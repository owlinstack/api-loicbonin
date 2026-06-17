# Documentation Technique Backend — ApiLoicBonin

Bienvenue dans la documentation technique du backend de l'application **loicbonin.com**. Ce projet sert d'API REST stateless au frontend Next.js et intègre un panel d'administration pour la gestion du contenu. Il est construit sur les standards modernes de Laravel 13 et PHP 8.4.

---

## 🧭 Table des Matières

Pour faciliter la prise en main et la maintenance du projet, voici le découpage de la documentation technique :

| **01** | [Stack Technique](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/documentation/01-stack.md) | Pile technologique (Laravel 13, Filament v5, PHP 8.4, SQLite/PostgreSQL, PHPStan, Laravel Pint). |
| **02** | [Architecture & Structure](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/documentation/02-architecture.md) | Couches applicatives (Contrôleurs, Services, DTOs, Enums, Models) et flux de données. |
| **03** | [Normes & Conventions](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/documentation/03-conventions.md) | Règles de typage strict, classes finales, sécurité (CORS & Rate Limiter), et standardisation du code (Pint/PHPStan). |
| **04** | [Administration Filament](file:///Users/loico/Work/MyDocs/dev/loicbonin.com/api-loicbonin/documentation/04-filament-admin.md) | Configuration du panel d'administration (AdminCreator), ressources CRUD, et formulaires sur-mesure (éditeurs de code/texte). |

---

## 🚀 Démarrage Rapide

### Prérequis

- **PHP** : version `8.4` (ou supérieure).
- **Composer** : gestionnaire de dépendances PHP officiel.
- **SQLite** : installé localement pour la base de données de développement.

### Installation locale

1. **Installer les dépendances de développement** :
   ```bash
   composer install
   ```

2. **Configurer l'environnement** :
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Migrer et alimenter la base de données (SQLite)** :
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

4. **Lancer le serveur de développement** :
   ```bash
   php artisan serve
   ```
   L'API sera accessible localement à l'adresse : [http://localhost:8000](http://localhost:8000).

### Outils de Qualité de Code

Avant de commiter des changements, veuillez lancer les validateurs automatiques du projet :
```bash
# Lancer les tests d'intégration API
php artisan test

# Lancer l'analyse statique de typage (Niveau 8)
./vendor/bin/phpstan analyse

# Lancer le formateur de code
./vendor/bin/pint --test
```
