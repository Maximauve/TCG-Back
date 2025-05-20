.DEFAULT_GOAL := help
USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: up
up: ## Start the project
	docker compose up -d

.PHONY: stop
stop: ## Stop the project
	docker compose stop

.PHONY: down
down: ## Stop and remove containers
	docker compose down

.PHONY: app-install
app-install: ## Install the app
	docker compose exec --user $(USER_ID):$(GROUP_ID) php /bin/bash -c "composer install"

.PHONY: app-cc
app-cc: ## Clear the cache
	docker compose exec --user $(USER_ID):$(GROUP_ID) php /bin/bash -c "php bin/console cache:clear"

.PHONY: app-ccc
app-ccc: ## Clear the cache - all
	docker compose exec --user $(USER_ID):$(GROUP_ID) php /bin/bash -c "php bin/console cache:clear && php bin/console cache:pool:clear --all"

.PHONY: app-migrate
app-migrate: ## Migrate database with latest migrations files
	docker compose exec --user $(USER_ID):$(GROUP_ID) php /bin/bash -c "php bin/console doctrine:migration:migrate --allow-no-migration"

.PHONY: app-migration-generate
app-migration-generate: ## Generate a migration file
	docker compose exec --user $(USER_ID):$(GROUP_ID) php /bin/bash -c "php bin/console make:migration"

.PHONY: app-connect
app-connect: ## Connect to the app container
	@docker compose exec -it --user $(USER_ID):$(GROUP_ID) php bash
