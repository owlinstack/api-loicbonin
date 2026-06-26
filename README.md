# API Loïc Bonin

Backend API REST stateless du portfolio [loicbonin.com](https://loicbonin.com), construit avec **Laravel 13** et **PHP 8.4+**.

## Stack principale

| Composant    | Version               |
| :----------- | :-------------------- |
| PHP          | 8.4+                  |
| Laravel      | 13.x                  |
| Filament     | 5.x                   |
| PHPStan      | Niveau max (Larastan) |
| Laravel Pint | PSR-12 strict         |
| SQLite       | Dev & tests           |

## Démarrage rapide

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

L'API est accessible sur `http://localhost:8000/api/v1/`.

## Qualité de code

```bash
php artisan test              # Tests
./vendor/bin/phpstan analyse  # Analyse statique (niveau max)
./vendor/bin/pint --test      # Formatage PSR-12
```

## Documentation technique

La documentation complète du projet est dans le dossier [`documentation/`](documentation/README.md).
