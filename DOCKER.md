# 🐳 Docker - Registre de Permanence

Guide complet pour exécuter l'application dans Docker.

## 📋 Prérequis

- Docker Engine 20.10+
- Docker Compose 2.x
- Git

## 🚀 Installation rapide

```bash
# 1. Cloner le projet
git clone <url-du-repo>
cd registre-permanence

# 2. Installation automatique
make install

# L'application est disponible sur http://localhost:8080
```

## 📦 Installation manuelle

### 1. Configuration

```bash
# Copier le fichier d'environnement
cp .env.docker .env

# Éditer si nécessaire
nano .env
```

### 2. Build et démarrage

```bash
# Construire les images
docker compose build

# Démarrer les conteneurs
docker compose up -d

# Vérifier le statut
docker compose ps
```

### 3. Initialisation Laravel

```bash
# Générer la clé d'application
docker compose exec php php artisan key:generate

# Exécuter les migrations
docker compose exec php php artisan migrate

# Créer le lien storage
docker compose exec php php artisan storage:link

# (Optionnel) Seed de la base
docker compose exec php php artisan db:seed
```

### 4. Créer un utilisateur admin

```bash
docker compose exec php php artisan make:filament-user
```

## 🛠️ Commandes utiles

### Avec Makefile

```bash
make help           # Voir toutes les commandes
make up             # Démarrer
make down           # Arrêter
make logs           # Voir les logs
make shell          # Shell dans PHP
make migrate        # Migrations
make fresh          # Reset DB
make cache          # Mettre en cache
make clear          # Vider les caches
```

### Sans Makefile

```bash
# Démarrer
docker compose up -d

# Arrêter
docker compose down

# Logs
docker compose logs -f

# Shell PHP
docker compose exec php bash

# Artisan
docker compose exec php php artisan <commande>

# Composer
docker compose exec php composer <commande>
```

## 🏗️ Architecture Docker

```
┌─────────────────────────────────────────────────┐
│                    NGINX                         │
│                 (Port 8080)                      │
└─────────────────────┬───────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────┐
│                   PHP-FPM                        │
│                Laravel 12                        │
│          Filament + Livewire + PDF              │
└─────────────┬────────────────┬──────────────────┘
              │                │
┌─────────────▼────┐  ┌────────▼────────────────┐
│      MySQL       │  │         Redis           │
│   (Port 3307)    │  │      (Port 6380)        │
└──────────────────┘  └─────────────────────────┘
```

## 📁 Services

| Service | Description | Port externe |
|---------|-------------|--------------|
| nginx | Serveur web | 8080 |
| php | PHP-FPM 8.4 | - |
| mysql | Base de données | 3307 |
| redis | Cache/Sessions | 6380 |
| queue | Worker de jobs | - |
| scheduler | Tâches planifiées | - |

## 🔧 Configuration

### Variables d'environnement importantes

| Variable | Description | Défaut |
|----------|-------------|--------|
| `APP_PORT` | Port de l'application | 8080 |
| `DB_PASSWORD` | Mot de passe MySQL | secret |
| `USER_ID` | UID utilisateur | 1000 |
| `GROUP_ID` | GID groupe | 1000 |

### Permissions (Linux)

Sur Linux, définissez les variables pour éviter les problèmes de permissions :

```bash
# Dans .env
USER_ID=1000   # Résultat de: id -u
GROUP_ID=1000  # Résultat de: id -g
```

## 🐛 Dépannage

### Erreur "Permission denied"

```bash
# Corriger les permissions
sudo chown -R $(id -u):$(id -g) storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### MySQL ne démarre pas

```bash
# Supprimer les volumes et recommencer
docker compose down -v
docker compose up -d
```

### Voir les logs d'erreur

```bash
# Logs PHP
docker compose logs -f php

# Logs Nginx
docker compose logs -f nginx

# Logs MySQL
docker compose logs -f mysql
```

### Reconstruire après modification

```bash
docker compose build --no-cache
docker compose up -d
```

## 📝 Mode développement (SQLite)

Pour un environnement léger sans MySQL/Redis :

```bash
# Créer la base SQLite
touch database/database.sqlite

# Lancer avec le compose de dev
docker compose -f docker-compose.dev.yml up -d
```

## 🔒 Production

Pour la production :

1. Définir `APP_ENV=production` dans `.env`
2. Définir `APP_DEBUG=false`
3. Utiliser des mots de passe forts
4. Activer HTTPS via un reverse proxy

```bash
# Optimisation production
docker compose exec php php artisan config:cache
docker compose exec php php artisan route:cache
docker compose exec php php artisan view:cache
```

## 📚 Tests

```bash
# Exécuter les tests
docker compose exec php php artisan test

# Ou via Make
make test
```

## 🗑️ Nettoyage complet

```bash
# Arrêter et supprimer tout (y compris les données)
docker compose down -v --rmi all

# Supprimer les images orphelines
docker system prune -a
```
