# Makefile pour Registre de Permanence
# Usage: make <commande>

.PHONY: help build up down restart logs shell migrate fresh seed test cache clear

# Variables
DOCKER_COMPOSE = docker compose
PHP_CONTAINER = registre_php
ARTISAN = $(DOCKER_COMPOSE) exec php php artisan

# Couleurs
GREEN = \033[0;32m
YELLOW = \033[1;33m
RED = \033[0;31m
NC = \033[0m

# Aide par défaut
help:
	@echo "$(GREEN)========================================$(NC)"
	@echo "$(GREEN) Registre de Permanence - Commandes    $(NC)"
	@echo "$(GREEN)========================================$(NC)"
	@echo ""
	@echo "$(YELLOW)Installation:$(NC)"
	@echo "  make install      - Installation complète (première fois)"
	@echo "  make build        - Construire les images Docker"
	@echo ""
	@echo "$(YELLOW)Gestion des conteneurs:$(NC)"
	@echo "  make up           - Démarrer les conteneurs"
	@echo "  make down         - Arrêter les conteneurs"
	@echo "  make restart      - Redémarrer les conteneurs"
	@echo "  make logs         - Afficher les logs"
	@echo "  make status       - État des conteneurs"
	@echo ""
	@echo "$(YELLOW)Développement:$(NC)"
	@echo "  make shell        - Shell dans le conteneur PHP"
	@echo "  make tinker       - Laravel Tinker"
	@echo "  make migrate      - Exécuter les migrations"
	@echo "  make fresh        - Reset DB + migrations + seeds"
	@echo "  make seed         - Exécuter les seeders"
	@echo ""
	@echo "$(YELLOW)Cache:$(NC)"
	@echo "  make cache        - Mettre en cache (config, routes, views)"
	@echo "  make clear        - Vider tous les caches"
	@echo ""
	@echo "$(YELLOW)Tests:$(NC)"
	@echo "  make test         - Exécuter les tests"
	@echo "  make lint         - Vérifier le code (Pint)"
	@echo ""

# Installation complète
install:
	@echo "$(GREEN)🚀 Installation de Registre de Permanence...$(NC)"
	@if [ ! -f .env ]; then cp .env.docker .env; echo "$(YELLOW)📄 Fichier .env créé$(NC)"; fi
	@$(DOCKER_COMPOSE) build --no-cache
	@$(DOCKER_COMPOSE) up -d
	@echo "$(YELLOW)⏳ Attente du démarrage des services...$(NC)"
	@sleep 10
	@$(ARTISAN) key:generate --force
	@$(ARTISAN) migrate --force
	@$(ARTISAN) db:seed --force || true
	@$(ARTISAN) storage:link --force || true
	@echo ""
	@echo "$(GREEN)========================================$(NC)"
	@echo "$(GREEN)✅ Installation terminée !$(NC)"
	@echo "$(GREEN)========================================$(NC)"
	@echo ""
	@echo "Application disponible sur: $(YELLOW)http://localhost:8080$(NC)"
	@echo ""

# Construire les images
build:
	@echo "$(YELLOW)🔨 Construction des images Docker...$(NC)"
	@$(DOCKER_COMPOSE) build

# Construire sans cache
build-no-cache:
	@echo "$(YELLOW)🔨 Construction des images Docker (sans cache)...$(NC)"
	@$(DOCKER_COMPOSE) build --no-cache

# Démarrer les conteneurs
up:
	@echo "$(GREEN)▶️  Démarrage des conteneurs...$(NC)"
	@$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)✅ Application disponible sur http://localhost:8080$(NC)"

# Démarrer avec logs
up-logs:
	@$(DOCKER_COMPOSE) up

# Arrêter les conteneurs
down:
	@echo "$(RED)⏹️  Arrêt des conteneurs...$(NC)"
	@$(DOCKER_COMPOSE) down

# Arrêter et supprimer les volumes
down-volumes:
	@echo "$(RED)⏹️  Arrêt des conteneurs et suppression des volumes...$(NC)"
	@$(DOCKER_COMPOSE) down -v

# Redémarrer
restart:
	@echo "$(YELLOW)🔄 Redémarrage des conteneurs...$(NC)"
	@$(DOCKER_COMPOSE) restart

# Logs
logs:
	@$(DOCKER_COMPOSE) logs -f

# Logs d'un service spécifique
logs-php:
	@$(DOCKER_COMPOSE) logs -f php

logs-nginx:
	@$(DOCKER_COMPOSE) logs -f nginx

logs-mysql:
	@$(DOCKER_COMPOSE) logs -f mysql

# État des conteneurs
status:
	@$(DOCKER_COMPOSE) ps

# Shell dans le conteneur PHP
shell:
	@$(DOCKER_COMPOSE) exec php bash

# Laravel Tinker
tinker:
	@$(ARTISAN) tinker

# Migrations
migrate:
	@echo "$(YELLOW)🗄️  Exécution des migrations...$(NC)"
	@$(ARTISAN) migrate

# Reset complet de la base
fresh:
	@echo "$(RED)🗄️  Reset de la base de données...$(NC)"
	@$(ARTISAN) migrate:fresh --seed

# Seeders
seed:
	@echo "$(YELLOW)🌱 Exécution des seeders...$(NC)"
	@$(ARTISAN) db:seed

# Cache
cache:
	@echo "$(YELLOW)📦 Mise en cache...$(NC)"
	@$(ARTISAN) config:cache
	@$(ARTISAN) route:cache
	@$(ARTISAN) view:cache
	@$(ARTISAN) event:cache
	@$(ARTISAN) icons:cache || true
	@echo "$(GREEN)✅ Cache mis à jour$(NC)"

# Vider les caches
clear:
	@echo "$(YELLOW)🧹 Nettoyage des caches...$(NC)"
	@$(ARTISAN) config:clear
	@$(ARTISAN) route:clear
	@$(ARTISAN) view:clear
	@$(ARTISAN) cache:clear
	@$(ARTISAN) event:clear
	@echo "$(GREEN)✅ Caches vidés$(NC)"

# Tests
test:
	@echo "$(YELLOW)🧪 Exécution des tests...$(NC)"
	@$(DOCKER_COMPOSE) exec php php artisan test

# Lint avec Pint
lint:
	@echo "$(YELLOW)🔍 Vérification du code...$(NC)"
	@$(DOCKER_COMPOSE) exec php ./vendor/bin/pint --test

# Fix avec Pint
lint-fix:
	@echo "$(YELLOW)🔧 Correction du code...$(NC)"
	@$(DOCKER_COMPOSE) exec php ./vendor/bin/pint

# Artisan (usage: make artisan cmd="route:list")
artisan:
	@$(ARTISAN) $(cmd)

# Composer (usage: make composer cmd="require package")
composer:
	@$(DOCKER_COMPOSE) exec php composer $(cmd)

# Créer un admin
create-admin:
	@$(ARTISAN) make:filament-user
