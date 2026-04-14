# Vivia — Plateforme de gestion associative

Application Laravel de gestion des adhérents, activités et inscriptions pour structures associatives, avec suivi des présences, gestion des paiements et exports.

## Fonctionnalités

- **Adhérents** — gestion des profils membres (informations personnelles, santé, régimes alimentaires), relations tuteurs/familles, suivi du cycle de vie (entrée, sortie, motifs)
- **Activités & Stages** — création et gestion des activités par niveau scolaire, capacité maximale, tarification, archivage
- **Inscriptions** — formulaires multi-étapes avec accès par token, intégration HelloAsso pour les paiements d'adhésion, gestion des quotas et réinscriptions
- **Présences** — gestion des séances par activité, appels, suivi des abandons et annulations
- **Administration** — backoffice avec invitations par token, rôles utilisateurs (admin, coordinateur, comptable), logs de synchronisation, statistiques
- **Exports** — génération de PDF (DOMPDF) et exports Excel (PHPSpreadsheet)
- **Emails** — notifications automatiques pour invitations, validations d'adhésion, annulations de cours et absences

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | Laravel 13, Livewire v4 |
| Frontend | TailwindCSS, Vite |
| Base de données | SQLite (défaut) / MySQL / MariaDB |
| PDF | barryvdh/laravel-dompdf |
| Exports | PHPSpreadsheet |
| Files d'attente | Queue Laravel (driver database) |

## Prérequis

- PHP ^8.3
- Composer
- Node.js / NPM
- SQLite (par défaut) ou MySQL / MariaDB
- Un serveur SMTP (pour les emails de notification)

## Installation

### 1. Cloner le dépôt

```bash
git clone https://git.unistra.fr/strebes/vivia.git
cd Vivia
```

### 2. Installer les dépendances manuellement

```bash
composer install
npm install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Puis éditer `.env` avec vos valeurs :

```env
APP_NAME=Vivia
APP_URL=http://127.0.0.1:8000

# SQLite (défaut — aucune configuration supplémentaire)
DB_CONNECTION=sqlite

# Ou MySQL / MariaDB
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=vivia
# DB_USERNAME=root
# DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
```

> Avec SQLite, le fichier `database/database.sqlite` est créé automatiquement. Avec MySQL, créez la base de données avant de continuer.

### 4. Migrations

```bash
php artisan migrate
```

### 5. Compiler les assets

```bash
npm run build
```

## Lancement en local

```bash
php artisan serve
```

L'application est accessible sur [http://127.0.0.1:8000](http://127.0.0.1:8000).

> En développement, utilisez `npm run dev`  à la place de `npm run build` pour le hot-reload. 

## Créer le premier compte administrateur

```bash
php artisan tinker
```

```php
User::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
    'role'     => 'admin',
]);
```

> Les autres utilisateurs sont créés via le système d'invitation depuis le backoffice.

## Rôles utilisateurs

| Rôle | Accès |
|---|---|
| `admin` | Accès complet au backoffice |
| `coordinateur` | Gestion des activités et de la ressourcerie |
| `animateur` | Gestion des présences à ses activités |
| `comptable` | Gestion des inscriptions |


