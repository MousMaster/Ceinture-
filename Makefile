# Makefile - Registre de Permanence
# Usage: make <commande>

.PHONY: help install build up down restart logs shell migrate fresh seed cache clear

COMPOSE = docker compose
ARTISAN = $(COMPOSE) exec php php artisan

help:
	@echo "=== Registre de Permanence ==="
	@echo ""
	@echo "Installation:"
	@echo "  make install    - Installation complete"
	@echo "  make build      - Construire les images"
	@echo ""
	@echo "Conteneurs:"
	@echo "  make up         - Demarrer"
	@echo "  make down       - Arreter"
	@echo "  make restart    - Redemarrer"
	@echo "  make logs       - Voir les logs"
	@echo "  make status     - Etat"
	@echo ""
	@echo "Laravel:"
	@echo "  make shell      - Shell PHP"
	@echo "  make migrate    - Migrations"
	@echo "  make fresh      - Reset DB"
	@echo "  make seed       - Seeders"
	@echo "  make cache      - Cache"
	@echo "  make clear      - Vider cache"

install:
	@echo ">>> Installation..."
	@cp -n .env.docker .env 2>/dev/null || true
	@mkdir -p database storage/app/public storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
	@touch database/database.sqlite
	@chmod -R 775 storage bootstrap/cache database
	@$(COMPOSE) build --no-cache
	@$(COMPOSE) up -d
	@echo ">>> Attente demarrage (10s)..."
	@sleep 10
	@$(ARTISAN) key:generate --force || true
	@$(ARTISAN) migrate --force
	@$(ARTISAN) db:seed --force || true
	@$(ARTISAN) storage:link --force || true
	@$(ARTISAN) filament:cache-components || true
	@echo ""
	@echo "=== Installation terminee ==="
	@echo "URL: http://localhost:8080"

build:
	@$(COMPOSE) build --no-cache

up:
	@$(COMPOSE) up -d
	@echo "URL: http://localhost:8080"

down:
	@$(COMPOSE) down

restart:
	@$(COMPOSE) restart

logs:
	@$(COMPOSE) logs -f

status:
	@$(COMPOSE) ps

shell:
	@$(COMPOSE) exec php sh

tinker:
	@$(ARTISAN) tinker

migrate:
	@$(ARTISAN) migrate

fresh:
	@$(ARTISAN) migrate:fresh --seed

seed:
	@$(ARTISAN) db:seed

cache:
	@$(ARTISAN) config:cache
	@$(ARTISAN) route:cache
	@$(ARTISAN) view:cache
	@$(ARTISAN) filament:cache-components || true

clear:
	@$(ARTISAN) config:clear
	@$(ARTISAN) route:clear
	@$(ARTISAN) view:clear
	@$(ARTISAN) cache:clear

test:
	@$(COMPOSE) exec php php artisan test

artisan:
	@$(ARTISAN) $(cmd)
