#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Restio Docker Startup${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Error: Docker is not running!${NC}"
    echo "Please start Docker Desktop and try again."
    exit 1
fi

# Stop any running containers
echo -e "${YELLOW}Stopping existing containers...${NC}"
docker-compose down

# Build images
echo -e "${YELLOW}Building Docker images...${NC}"
docker-compose build --pull

# Start services
echo -e "${YELLOW}Starting services...${NC}"
docker-compose up -d

# Wait for app container to be fully ready
echo -e "${YELLOW}Waiting for application setup...${NC}"
echo "The entrypoint script will:"
echo "  - Install composer dependencies"
echo "  - Wait for database"
echo "  - Run migrations"
echo "  - Optimize caches"
echo ""
sleep 10

# Check app logs to see if setup is complete
docker-compose logs app | tail -20

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✓ Docker Environment Ready!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Application:  ${GREEN}http://localhost${NC}"
echo -e "Admin Panel:  ${GREEN}http://localhost/admin${NC}"
echo -e "  Email:      ${YELLOW}admin@restio.com${NC}"
echo -e "  Password:   ${YELLOW}password${NC}"
echo ""
echo -e "Horizon:      ${GREEN}http://localhost/horizon${NC}"
echo ""
echo -e "Useful commands:"
echo -e "  View logs:        ${YELLOW}docker-compose logs -f${NC}"
echo -e "  Stop:             ${YELLOW}docker-compose down${NC}"
echo -e "  Restart:          ${YELLOW}docker-compose restart${NC}"
echo -e "  Run artisan:      ${YELLOW}docker-compose exec app php artisan [command]${NC}"
echo -e "  Run composer:     ${YELLOW}docker-compose exec app composer [command]${NC}"
echo -e "  Access database:  ${YELLOW}docker-compose exec db psql -U laravel -d laravel${NC}"
echo -e "  Fresh migrations: ${YELLOW}docker-compose exec app php artisan migrate:fresh --seed${NC}"
echo ""
