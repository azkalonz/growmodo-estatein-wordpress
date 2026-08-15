SHELL := /bin/bash
ENV_FILE ?= .env
COMPOSE := docker compose --env-file $(ENV_FILE)

.PHONY: help env config up install seed export package wasmer audit lint lint-php lint-js lint-css phpcs test test-e2e screenshots lighthouse qa-plugins logs down

help:
	@echo "Estatein development commands"
	@echo "  make env          Create a local .env from the documented example"
	@echo "  make config       Validate the resolved Docker Compose model"
	@echo "  make install      Build, install WordPress, activate dependencies, and seed demo content"
	@echo "  make seed         Re-run the idempotent fixture"
	@echo "  make export       Export an equivalent WXR demo-content file"
	@echo "  make package      Build and verify installable theme/plugin ZIPs"
	@echo "  make wasmer       Build and validate the complete Wasmer handoff"
	@echo "  make audit        Check locked PHP and Node dependencies for advisories"
	@echo "  make lint         Run PHP, JavaScript, and CSS static checks"
	@echo "  make test-e2e     Run Playwright in Chromium, Firefox, and WebKit"
	@echo "  make screenshots  Capture the 390, 1440, and 1920 reference widths"
	@echo "  make lighthouse   Enforce the local Accessibility/SEO/Best Practices gate"

env:
	@test -f $(ENV_FILE) || cp .env.example $(ENV_FILE)
	@echo "Review $(ENV_FILE) and replace every placeholder before make install."

config:
	@$(COMPOSE) config --quiet

up:
	@$(COMPOSE) up --detach --build db mailpit wordpress

install:
	@ESTATEIN_ENV_FILE=$(abspath $(ENV_FILE)) ./scripts/bootstrap.sh

seed:
	@ESTATEIN_ENV_FILE=$(abspath $(ENV_FILE)) ./scripts/seed.sh

export:
	@ESTATEIN_ENV_FILE=$(abspath $(ENV_FILE)) ./scripts/export-demo.sh

package:
	@./scripts/package.sh

wasmer: package
	@./scripts/check-wasmer-readiness.sh

audit:
	@composer audit --locked
	@npm run audit

lint: lint-php phpcs lint-js lint-css

lint-php:
	@./scripts/check-php-syntax.sh

phpcs:
	@composer phpcs

lint-js:
	@npm run lint:js

lint-css:
	@npm run lint:css

test: test-e2e

test-e2e:
	@npm run test:e2e

screenshots:
	@npm run screenshots

lighthouse:
	@npm run lighthouse

qa-plugins:
	@ESTATEIN_ENV_FILE=$(abspath $(ENV_FILE)) ./scripts/install-qa-plugins.sh

logs:
	@$(COMPOSE) logs --follow --tail=200 wordpress db mailpit

down:
	@$(COMPOSE) down
