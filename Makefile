# User-related variables
HOST_USER = $(USER)
HOST_UID = $(shell id -u $(USER))
HOST_GID = $(shell id -g $(USER))

# Temporary directory used to store cache and history
TEMP_DIR = $(PWD)/.tmp
NPM_CACHE_DIR = $(TEMP_DIR)/npm/
BASH_DIR = $(TEMP_DIR)/bash
BASH_HISTORY_FILE = $(BASH_DIR)/.bash_history
BASHRC_FILE = $(BASH_DIR)/.bashrc
ENVIRONMENT ?= development

export BUILD_ENVIRONMENT := ${ENVIRONMENT}
export HOST_USER := ${HOST_USER}
export HOST_UID := ${HOST_UID}
export HOST_GID := ${HOST_GID}

# We create local npm cache folder to make development experience smoother
config-npm:
	@echo "Configuring npm cache directory: $(NPM_CACHE_DIR)"

	$(shell mkdir -p $(NPM_CACHE_DIR))
	
	$(shell chown $(HOST_UID):$(HOST_GID) $(NPM_CACHE_DIR))

# We create a local shell history file if it doesn't exist
config-shell:
	@echo "Configuring bash history files..."

	$(shell mkdir -p $(BASH_DIR))

	$(shell if [ ! -f $(BASH_HISTORY_FILE) ]; then touch $(BASH_HISTORY_FILE); chown $(HOST_UID):$(HOST_GID) $(BASH_HISTORY_FILE); fi)

	$(shell if [ ! -f $(BASHRC_FILE) ]; then touch $(BASHRC_FILE); chown $(HOST_UID):$(HOST_GID) $(BASHRC_FILE); fi)

# Build the application Docker image
build:
	@echo "Building application ${BUILD_ENVIRONMENT} docker image..."

ifeq ($(BUILD_ENVIRONMENT), development)
# This will use both docker-compose files
	docker compose build
else
	docker compose -f docker-compose.yml build
endif

# Start all the containers required to run the application
start: stop build
	@echo "Starting the application ${BUILD_ENVIRONMENT} container..."

ifeq ($(BUILD_ENVIRONMENT), development)
# This will use both docker-compose files
	docker compose up -d
else
	docker compose -f docker-compose.yml up -d
endif

# Tail the logs from app, nginx, and pgsql containers
logs:
	@echo "Tailing logs..."
	
	docker compose logs --tail="all" --follow

# Stop all application running containers and remove them
stop:
	@echo "Stopping the application..."

	docker compose down

clean: stop
	@echo "Cleaning..."

	rm -rf node_modules vendor public/build

# You can run npm/composer/artisan commands and use the Tinker console
bash: config-shell
	docker compose run --rm app bash

db-migrate:
	@echo "Running database migrations in ${BUILD_ENVIRONMENT}..."
	
ifeq ($(BUILD_ENVIRONMENT), development)
# This will use both docker-compose files
	docker compose run --rm app php artisan migrate
else
	docker compose -f docker-compose.yml run --rm app php artisan migrate
endif

db-seed:
	@echo "Running database migrations in ${BUILD_ENVIRONMENT}..."
	
ifeq ($(BUILD_ENVIRONMENT), development)
# This will use both docker-compose files
	docker compose run --rm app php artisan db:seed
else
	docker compose -f docker-compose.yml run --rm app php artisan db:seed
endif

# Install all npm packages and generate the bundle
npm-install: config-npm
	@echo "Installing node packages and bundling assets..."

	docker compose run --rm app npm install

# Install all npm packages and generate the bundle
npm-build: config-npm
	@echo "Installing node packages and bundling assets..."

	docker compose run --rm app npm run build

# Install all PHP composer packages
composer-install:
	@echo "Installing composer packages..."

	docker compose run --rm app composer install