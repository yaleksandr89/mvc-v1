PROJECT ?= mvc-v1
ENV_FILE ?= .env.docker
COMPOSE = docker compose -p $(PROJECT) --env-file $(ENV_FILE)
CMD ?=

SERVICES := php nginx postgres
SERVICE_TARGETS := restart log in
SERVICE_TARGET := $(firstword $(filter $(SERVICE_TARGETS),$(MAKECMDGOALS)))

ifneq ($(SERVICE_TARGET),)
ifneq ($(firstword $(MAKECMDGOALS)),$(SERVICE_TARGET))
$(error Service target must be the first goal)
endif
SERVICE := $(word 2,$(MAKECMDGOALS))
EXTRA_SERVICE_ARGS := $(wordlist 3,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
ifeq ($(SERVICE),)
$(error Usage: make $(SERVICE_TARGET) <php|nginx|postgres>)
endif
ifneq ($(filter $(SERVICE),$(SERVICES)),$(SERVICE))
$(error Unknown service "$(SERVICE)". Allowed: $(SERVICES))
endif
ifneq ($(EXTRA_SERVICE_ARGS),)
$(error Expected exactly one service argument)
endif
.PHONY: $(SERVICE)
$(SERVICE):
	@:
endif

.DEFAULT_GOAL := help
.PHONY: help init check-env config build up down restart ps log log-all in php composer composer-install test analyse check smoke db-check demo-data postgres-reinit

help:
	@printf '%s\n' 'Bootstrap / Первичная настройка:'
	@printf '%s\n' '  make init                              Create .env.docker and logs/ / Создать .env.docker и logs/'
	@printf '%s\n' '  make check-env                         Validate Docker environment / Проверить Docker-окружение'
	@printf '%s\n' ''
	@printf '%s\n' 'Docker lifecycle / Управление Docker:'
	@printf '%s\n' '  make config                            Validate Compose config / Проверить Compose-конфигурацию'
	@printf '%s\n' '  make build                             Build PHP dev image / Собрать dev-образ PHP'
	@printf '%s\n' '  make up                                Start the development stack / Запустить dev-окружение'
	@printf '%s\n' '  make down                              Stop stack, preserve data / Остановить stack, сохранить данные'
	@printf '%s\n' '  make restart <service>                 Restart php, nginx or postgres / Перезапустить сервис'
	@printf '%s\n' '  make ps                                Show project containers / Показать контейнеры'
	@printf '%s\n' ''
	@printf '%s\n' 'Diagnostics / Диагностика:'
	@printf '%s\n' '  make log <service>                     Follow one service log / Смотреть лог сервиса'
	@printf '%s\n' '  make log-all                           Follow all project logs / Смотреть все логи'
	@printf '%s\n' '  make in <service>                      Open a non-root service shell / Открыть shell сервиса без root'
	@printf '%s\n' '  make db-check                          Check PostgreSQL schema/data state / Проверить состояние схемы/данных PostgreSQL'
	@printf '%s\n' '  make demo-data                         Load 50 demo articles into an empty database / Загрузить 50 демо-статей в пустую базу'
	@printf '%s\n' '  make smoke                             Run full CRUD/CSRF smoke / Запустить CRUD/CSRF smoke'
	@printf '%s\n' ''
	@printf '%s\n' 'PHP / Composer / Quality:'
	@printf '%s\n' '  make php CMD="..."                     Run PHP as app / Запустить PHP от app'
	@printf '%s\n' '  make composer CMD="..."                Run Composer as app / Запустить Composer от app'
	@printf '%s\n' '  make composer-install                  Install dev dependencies from lock / Установить зависимости из lock'
	@printf '%s\n' '  make test | analyse | check            Run existing Composer quality scripts / Запустить проверки Composer'
	@printf '%s\n' ''
	@printf '%s\n' 'Destructive maintenance / Деструктивные операции:'
	@printf '%s\n' '  make postgres-reinit CONFIRM=postgres18 Recreate PostgreSQL volume with an empty schema / Пересоздать volume PostgreSQL с пустой схемой'
	@printf '%s\n' '  make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1 Recreate volume and load 50 demo articles / Пересоздать volume PostgreSQL и загрузить 50 демо-статей'

init:
	@if [ ! -f "$(ENV_FILE)" ]; then \
		cp .env.docker.example "$(ENV_FILE)"; \
		sed -i "s/^HOST_UID=.*/HOST_UID=$$(id -u)/; s/^HOST_GID=.*/HOST_GID=$$(id -g)/" "$(ENV_FILE)"; \
		printf '%s\n' 'Created .env.docker from .env.docker.example'; \
	else \
		printf '%s\n' '.env.docker already exists; leaving it unchanged'; \
	fi
	@mkdir -p logs

check-env:
	@test -f "$(ENV_FILE)" || (printf '%s\n' 'Missing .env.docker. Run: make init' >&2; exit 1)
	@docker compose version >/dev/null

config: check-env
	@$(COMPOSE) config

build: check-env
	@$(COMPOSE) build

up: check-env
	@$(COMPOSE) up -d --wait

down: check-env
	@$(COMPOSE) down --remove-orphans

restart: check-env $(SERVICE)
	@$(COMPOSE) restart $(SERVICE)

ps: check-env
	@$(COMPOSE) ps

log: check-env $(SERVICE)
	@$(COMPOSE) logs -f --tail=100 $(SERVICE)

log-all: check-env
	@$(COMPOSE) logs -f --tail=100

in: check-env $(SERVICE)
	@$(if $(filter php,$(SERVICE)),$(COMPOSE) exec --user app php bash,$(if $(filter nginx,$(SERVICE)),$(COMPOSE) exec --user nginx nginx sh,$(COMPOSE) exec --user postgres postgres sh))

ifeq ($(filter php,$(SERVICE)),)
php: check-env
	@test -n "$(CMD)" || (printf '%s\n' 'Set CMD, e.g. make php CMD="-v"' >&2; exit 1)
	@$(COMPOSE) exec --user app php php $(CMD)
endif

composer: check-env
	@test -n "$(CMD)" || (printf '%s\n' 'Set CMD, e.g. make composer CMD="validate"' >&2; exit 1)
	@$(COMPOSE) exec --user app php composer $(CMD)

composer-install: check-env
	@$(COMPOSE) exec --user app php composer install --no-interaction --prefer-dist

test: check-env
	@$(COMPOSE) exec --user app php composer test

analyse: check-env
	@$(COMPOSE) exec --user app php composer analyse

check: check-env
	@$(COMPOSE) exec --user app php composer check

smoke: check-env
	@count=$$($(COMPOSE) exec -T --user postgres postgres sh -lc 'psql -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" -Atc "SELECT COUNT(*) FROM blog_posts"'); \
	test "$$count" = 50 || { printf '%s\n' 'Demo data is required. Run: make demo-data' >&2; exit 1; }
	@$(COMPOSE) exec --user app php php tests/runtime-smoke.php

db-check: check-env
	@$(COMPOSE) exec --user postgres postgres sh -lc 'psql -v ON_ERROR_STOP=1 -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" -Atc "SHOW server_version; SELECT COUNT(*), MIN(id), MAX(id) FROM blog_posts;"'

demo-data: check-env
	@count=$$($(COMPOSE) exec -T --user postgres postgres sh -lc 'psql -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" -Atc "SELECT COUNT(*) FROM blog_posts"'); \
	test "$$count" = 0 || { printf 'Refusing demo data load: blog_posts contains %s rows.\n' "$$count" >&2; exit 1; }
	@$(COMPOSE) exec -T --user postgres postgres sh -lc 'psql -v ON_ERROR_STOP=1 -U "$$POSTGRES_USER" -d "$$POSTGRES_DB"' < docs/db_demo_data.sql
	@stats=$$($(COMPOSE) exec -T --user postgres postgres sh -lc 'psql -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" -Atc "SELECT COUNT(*), MIN(id), MAX(id) FROM blog_posts"'); \
	test "$$stats" = '50|1|50' || { printf 'Expected 50|1|50 after demo data load, got %s\n' "$$stats" >&2; exit 1; }

postgres-reinit: check-env
	@test "$(CONFIRM)" = postgres18 || (printf '%s\n' 'Refusing reset. Re-run with: make postgres-reinit CONFIRM=postgres18' >&2; exit 1)
	@volume="$(PROJECT)_postgres-data"; \
	if docker volume inspect "$$volume" >/dev/null 2>&1; then \
		project_label=$$(docker volume inspect -f '{{ index .Labels "com.docker.compose.project" }}' "$$volume"); \
		volume_label=$$(docker volume inspect -f '{{ index .Labels "com.docker.compose.volume" }}' "$$volume"); \
		test "$$project_label" = "$(PROJECT)" && test "$$volume_label" = postgres-data || { printf '%s\n' 'Refusing to remove a volume with unexpected Compose labels' >&2; exit 1; }; \
	fi
	@$(COMPOSE) down --remove-orphans
	@volume="$(PROJECT)_postgres-data"; \
	if docker volume inspect "$$volume" >/dev/null 2>&1; then docker volume rm "$$volume"; else printf '%s\n' "PostgreSQL volume $$volume does not exist; nothing to remove"; fi
	@$(COMPOSE) up -d --wait
	@if [ "$(WITH_DEMO_DATA)" = 1 ]; then \
		$(MAKE) PROJECT="$(PROJECT)" ENV_FILE="$(ENV_FILE)" demo-data; \
	fi
	@$(MAKE) PROJECT="$(PROJECT)" ENV_FILE="$(ENV_FILE)" db-check
