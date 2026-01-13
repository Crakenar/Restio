.PHONY: test test-keep test-db-up test-db-down test-db-restart

# Run tests with PostgreSQL (stops DB after tests)
test:
	@./test.sh

# Run tests with PostgreSQL (keeps DB running after tests)
test-keep:
	@./test.sh --keep

# Run specific test file or filter
test-filter:
	@./test.sh --filter=$(filter)

# Start test database only
test-db-up:
	@docker compose -f docker-compose.testing.yml up -d
	@echo "Waiting for database to be ready..."
	@for i in {1..30}; do \
		if docker compose -f docker-compose.testing.yml exec -T db-test pg_isready -U laravel_test > /dev/null 2>&1; then \
			echo "Database is ready!"; \
			break; \
		fi; \
		if [ $$i -eq 30 ]; then \
			echo "Database failed to start"; \
			exit 1; \
		fi; \
		sleep 1; \
	done

# Stop test database
test-db-down:
	@docker compose -f docker-compose.testing.yml down

# Restart test database
test-db-restart:
	@docker compose -f docker-compose.testing.yml down
	@docker compose -f docker-compose.testing.yml up -d

# Run migrations on test database
test-migrate:
	@php artisan migrate:fresh --env=testing

# Run tests without Docker (uses existing database)
test-local:
	@php artisan test --env=testing
