#!/bin/bash

# Laravel Backup Helper Script for Docker
# This script runs backup commands inside the Docker container

set -e

CONTAINER="restio-scheduler-1"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

function print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

function print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

function print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER}$"; then
    print_error "Container ${CONTAINER} is not running"
    exit 1
fi

case "${1:-help}" in
    run)
        print_info "Running backup..."
        docker exec ${CONTAINER} php artisan backup:run
        ;;
    run-db-only)
        print_info "Running database-only backup..."
        docker exec ${CONTAINER} php artisan backup:run --only-db
        ;;
    run-files-only)
        print_info "Running files-only backup..."
        docker exec ${CONTAINER} php artisan backup:run --only-files
        ;;
    list)
        print_info "Listing backups..."
        docker exec ${CONTAINER} php artisan backup:list
        ;;
    monitor)
        print_info "Monitoring backup health..."
        docker exec ${CONTAINER} php artisan backup:monitor
        ;;
    clean)
        print_info "Cleaning old backups..."
        docker exec ${CONTAINER} php artisan backup:clean
        ;;
    restore)
        if [ -z "$2" ]; then
            print_error "Please provide backup filename"
            print_info "Usage: $0 restore <backup-filename>"
            exit 1
        fi
        print_warning "Starting restoration process for: $2"
        print_warning "This will restore the database from the backup!"
        read -p "Are you sure? (yes/no): " confirm
        if [ "$confirm" == "yes" ]; then
            docker exec ${CONTAINER} php artisan db:restore "$2"
        else
            print_info "Restoration cancelled"
        fi
        ;;
    help|*)
        echo "Laravel Backup Helper Script"
        echo ""
        echo "Usage: $0 [command]"
        echo ""
        echo "Commands:"
        echo "  run              - Run full backup (database + files)"
        echo "  run-db-only      - Run database-only backup"
        echo "  run-files-only   - Run files-only backup"
        echo "  list             - List all backups"
        echo "  monitor          - Monitor backup health"
        echo "  clean            - Clean old backups"
        echo "  restore <file>   - Restore from backup file"
        echo "  help             - Show this help message"
        ;;
esac
