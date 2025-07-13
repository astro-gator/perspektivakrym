#!/usr/bin/make -f
# SHELL = /bin/sh

ENVFILE=.env
MAKEFILE_PATH=.
DC_LOCAL_FILE=./docker-compose.yml
DC_TEST_FILE=./docker-compose-test.yml

ifneq ("$(wildcard ./app/.env)","")
	# ENVFILE=.env
	APP_DIR=$(shell echo $$(cd . && pwd))
else
	# ENVFILE=../.env
	APP_DIR=$(shell echo $$(cd .. && pwd))
	MAKEFILE_PATH=$(shell basename $$(cd . && pwd))
endif

SHELL = /bin/sh
.DEFAULT_GOAL = help
.PHONY: help down up clear update build first_install tests_run

DC=
COPY=cp
RM=rm -rf

help:
	@echo Usage:
	@echo "   make <command> [<command>, [<command>, ...]]"
	@echo -----
	@echo Available commands:
	@echo "   up             Up projects"
	@echo "   down           Down projects"
	@echo "   update         Update docker images"
	@echo "   build          Build docker images"
	@echo "   first_install  First installation of the project"
	@echo "   clear   Clear docker system folder"
	@echo "   tests_run   	 Run tests"
	@echo Settings:
	@echo "   APP_DIR:              $(APP_DIR)"
	@echo -----
	@echo Example:
	@echo "   make up          "
	@echo "   make down up     "
	@echo "   make update up   "
	@echo -----

down:
	cd $(APP_DIR) && docker-compose down -v --remove-orphans

up:
	docker rm -f $$(docker ps -a | grep orders | awk '{print $$1}') || echo
	cd $(APP_DIR) && docker-compose up -d --remove-orphans --force-recreate
	cd $(APP_DIR) && $(DC) docker-compose exec -T php /bin/bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install --prefer-dist --no-ansi --no-scripts --no-interaction --no-progress"
	cd $(APP_DIR) && $(DC) docker-compose exec -u root -T php /bin/bash -c "chown www-data:www-data storage/logs/*"
	cd $(APP_DIR) && $(DC) docker-compose exec -T php /bin/bash -c "php artisan key:generate"
	#$(MAKE) migrate
	@echo ---------------------------------------------
	@echo =============================================
	@echo == Done
	@echo =============================================

clear:
	docker-compose down -v --remove-orphans
	docker container prune -f
	docker image prune -f
	docker volume prune -f
	docker network prune -f
	docker network create orders-network || echo Created
	@echo "======================="

update:
	cd $(APP_DIR) && docker-compose pull

build:
	cd $(APP_DIR) && docker-compose build

first_install:
	cp ./app/.env.dev ./app/.env
	docker network create guare-network || echo Created
	$(MAKE) build
	$(MAKE) up

tests_run:
	docker-compose -f $(DC_LOCAL_FILE) up -d
	docker-compose -f $(DC_LOCAL_FILE) exec -T php /bin/bash -c "php artisan --env=testing migrate:fresh --seed"
	docker-compose -f $(DC_LOCAL_FILE) exec php /bin/bash -c "php artisan test" || echo "TEST FAIL"
	docker-compose -f $(DC_TEST_FILE) down

exec-bash:
	docker-compose exec -T php bash -c "$(cmd)"

migrate:
	@make exec-bash cmd="php artisan migrate --force"

cs:
	@make exec-bash cmd="composer cs"

cs-fix:
	@make exec-bash cmd="composer cs-fix"

sh:
	cd $(APP_DIR) && docker-compose exec -it php /bin/bash
