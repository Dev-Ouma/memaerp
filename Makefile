.DEFAULT_GOAL := help
SHELL := /bin/bash
API := apps/api

.PHONY: help
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

.PHONY: setup
setup: ## First-time setup: services, dependencies, database, seed data
	docker compose up -d
	@echo "waiting for postgres..."
	@until docker compose exec -T postgres pg_isready -U memaerp -d memaerp >/dev/null 2>&1; do sleep 1; done
	cd $(API) && composer install
	cd $(API) && [ -f .env ] || cp .env.example .env
	cd $(API) && php artisan key:generate
	cd $(API) && php artisan migrate:fresh --seed
	pnpm install
	@echo ""
	@echo "  Ready. Run 'make dev'."

.PHONY: up
up: ## Start backing services
	docker compose up -d

.PHONY: down
down: ## Stop backing services
	docker compose down

.PHONY: dev
dev: ## Run API and all frontend apps
	@trap 'kill 0' EXIT; \
	(cd $(API) && php artisan serve --port=8000) & \
	(cd $(API) && php artisan queue:work --queue=critical,high,default,low) & \
	pnpm dev & \
	wait

.PHONY: migrate
migrate: ## Run pending migrations
	cd $(API) && php artisan migrate

.PHONY: fresh
fresh: ## Rebuild the database from scratch and seed
	cd $(API) && php artisan migrate:fresh --seed

.PHONY: e2e-portals
e2e-portals: ## Run cross-portal login smoke tests (3000, 3001, 3002, 3005)
	npx playwright test --config=playwright.portals.config.ts

.PHONY: e2e-audit
e2e-audit: ## Run portal load/accessibility audit across all frontends
	node scripts/e2e-audit.js

.PHONY: e2e-admin
e2e-admin: ## Run admin workflow Playwright tests (IAM, institution, curriculum, course)
	npx playwright test --config=playwright.iam.config.ts

.PHONY: test
test: ## Run the full test suite
	cd $(API) && php artisan test
	pnpm test

.PHONY: api-test
api-test: ## Run the backend test suite only
	cd $(API) && php artisan test

.PHONY: api-quality
api-quality: ## Backend formatting, static analysis and module boundaries (what CI runs)
	cd $(API) && ./vendor/bin/pint --test
	cd $(API) && ./vendor/bin/phpstan analyse --memory-limit=1G --no-progress
	cd $(API) && ./vendor/bin/deptrac analyse --config-file=deptrac.yaml --no-progress

.PHONY: api-baseline
api-baseline: ## Re-record the module boundary debt ledger (only ever to shrink it)
	cd $(API) && ./vendor/bin/deptrac analyse --config-file=deptrac.yaml --no-progress \
		--formatter=baseline --output=deptrac-baseline.yaml

.PHONY: quality
quality: ## Formatting, static analysis and module boundary checks
	cd $(API) && ./vendor/bin/pint --test
	cd $(API) && ./vendor/bin/phpstan analyse --memory-limit=1G
	cd $(API) && ./vendor/bin/deptrac analyse --config-file=deptrac.yaml --no-progress
	pnpm lint
	pnpm typecheck

.PHONY: fix
fix: ## Auto-fix formatting
	cd $(API) && ./vendor/bin/pint
	pnpm format

.PHONY: fresh-restore-test
fresh-restore-test: ## Gate 0 requirement: prove a backup restores, and time it
	./scripts/restore-drill.sh

.PHONY: backup
backup: ## Create a checksummed PostgreSQL custom-format backup
	./scripts/database-backup.sh

.PHONY: database-verify
database-verify: ## Verify migrations, rollback safety, and database architecture tests
	cd $(API) && php artisan migrate:fresh --seed
	cd $(API) && php artisan test --testsuite=Feature --filter=DatabaseArchitectureTest
	cd $(API) && php artisan migrate:rollback --step=4
	cd $(API) && php artisan migrate
