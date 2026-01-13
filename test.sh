#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Load .env.testing
if [ -f .env.testing ]; then
    export $(grep -v '^#' .env.testing | xargs)
fi

# Start the test database
echo -e "${YELLOW}Starting test database...${NC}"
docker compose -f docker-compose.testing.yml up -d

# Wait for database to be healthy
echo -e "${YELLOW}Waiting for database to be ready...${NC}"
for i in {1..30}; do
    if docker compose -f docker-compose.testing.yml exec -T db-test pg_isready -U laravel_test > /dev/null 2>&1; then
        echo -e "${GREEN}Database is ready!${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${RED}Database failed to start${NC}"
        exit 1
    fi
    sleep 1
done

# Run migrations
echo -e "${YELLOW}Running migrations...${NC}"
php artisan migrate:fresh --env=testing

# Run tests
echo -e "${YELLOW}Running tests...${NC}"
if [ -z "$1" ]; then
    php artisan test --env=testing
else
    php artisan test --env=testing "$@"
fi

TEST_EXIT_CODE=$?

# Keep database running if tests failed or if --keep flag is passed
if [[ " $@ " =~ " --keep " ]] || [ $TEST_EXIT_CODE -ne 0 ]; then
    if [ $TEST_EXIT_CODE -ne 0 ]; then
        echo -e "${RED}Tests failed. Keeping database running for debugging.${NC}"
    else
        echo -e "${GREEN}Keeping database running as requested.${NC}"
    fi
    echo -e "${YELLOW}To stop the test database, run: docker compose -f docker-compose.testing.yml down${NC}"
else
    echo -e "${YELLOW}Stopping test database...${NC}"
    docker compose -f docker-compose.testing.yml down
fi

exit $TEST_EXIT_CODE
