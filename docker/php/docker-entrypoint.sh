#!/bin/bash
set -e

echo "========================================"
echo " Registre de Permanence - Démarrage"
echo "========================================"

# Attendre que la base de données soit prête
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Attente de MySQL sur $DB_HOST:${DB_PORT:-3306}..."
    timeout=60
    counter=0
    until nc -z "$DB_HOST" "${DB_PORT:-3306}" 2>/dev/null; do
        counter=$((counter + 1))
        if [ $counter -ge $timeout ]; then
            echo "Timeout: MySQL non disponible après ${timeout}s"
            break
        fi
        sleep 1
    done
    echo "MySQL est prêt"
fi

# Création des répertoires nécessaires
echo "Création des répertoires..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Génération de la clé d'application si nécessaire
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Génération de la clé d'application..."
    php artisan key:generate --force --no-interaction
fi

# Cache des configurations en production
if [ "$APP_ENV" = "production" ]; then
    echo "Optimisation pour la production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan icons:cache 2>/dev/null || true
fi

# Lien symbolique pour le storage
if [ ! -L public/storage ]; then
    echo "Création du lien storage..."
    php artisan storage:link --force --no-interaction || true
fi

# Migrations (seulement si DB est configurée)
if [ -n "$DB_HOST" ] || [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "Exécution des migrations..."
    php artisan migrate --force --no-interaction || true
fi

echo "========================================"
echo " Application prête !"
echo "========================================"

# Exécution de la commande passée au conteneur
exec "$@"
