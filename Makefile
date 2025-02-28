# Makefile for Symfony commands in the dominus-admin container

CONTAINER_NAME=dominus-admin
CONSOLE=bin/console
NETWORK_NAME=dominus

# Default target
.PHONY: help
help:
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@echo "  symfony-cmd CMD=<command>  Run a Symfony command in the dominus-admin container"

.PHONY: check-network
check-network:
	@if ! docker network inspect $(NETWORK_NAME) >/dev/null 2>&1; then \
		echo "Network $(NETWORK_NAME) not found. Creating it... "; \
		docker network create $(NETWORK_NAME); \
	else \
		echo "Network $(NETWORK_NAME) already exists."; \
	fi

.PHONY: up
up: check-network
	docker compose up -d --build

.PHONY: down
down:
	docker compose down --remove-orphans

.PHONY: restart
restart: down up


# Run Symfony command in the container
.PHONY: symfony-cmd
symfony-cmd:
	@if [ -z "$(CMD)" ]; then \
		echo "Error: CMD variable is not set"; \
		echo "Usage: make symfony-cmd CMD=<command>"; \
		exit 1; \
	fi
	docker compose exec -it $(CONTAINER_NAME) $(CONSOLE) $(CMD)

