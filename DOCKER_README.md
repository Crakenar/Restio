# Docker Setup - Complete Guide

## The Problem You Had

You were getting this error:
```
filament/support v3.3.47 requires ext-intl * -> it is missing from your system
```

**Why?** You were running `composer` on your HOST machine, which doesn't have the `intl` PHP extension. Docker has ALL the extensions needed.

## The Solution

**ALWAYS run composer and artisan inside Docker, NEVER on your host.**

## How to Start Your Application

### 1. Start Docker Environment

```bash
./docker-start.sh
```

This single command will:
- Stop any existing containers
- Build Docker images with ALL PHP extensions (intl, redis, pdo_pgsql, gd, zip, etc.)
- Start all services (App, Nginx, PostgreSQL, Redis, Horizon, Scheduler)
- Install composer dependencies inside Docker
- Wait for database to be ready
- Run migrations automatically
- Optimize caches
- Set up storage links

### 2. Access Your Application

- **Main App**: http://localhost
- **Admin Panel**: http://localhost/admin
  - Email: `admin@restio.com`
  - Password: `password`
- **Horizon Dashboard**: http://localhost/horizon

## Running Commands

### Composer Commands (INSIDE DOCKER)

```bash
# Install dependencies
docker-compose exec app composer install

# Add a package
docker-compose exec app composer require vendor/package

# Update dependencies
docker-compose exec app composer update

# Remove a package
docker-compose exec app composer remove vendor/package
```

### Artisan Commands (INSIDE DOCKER)

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Fresh migrations with seeding
docker-compose exec app php artisan migrate:fresh --seed

# Clear caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear

# Create models, controllers, etc.
docker-compose exec app php artisan make:model ModelName
docker-compose exec app php artisan make:controller ControllerName
```

### Database Access

```bash
# Access PostgreSQL CLI
docker-compose exec db psql -U laravel -d laravel

# Run SQL query directly
docker-compose exec db psql -U laravel -d laravel -c "SELECT * FROM users;"
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f horizon
```

## Common Tasks

### Stop Everything

```bash
docker-compose down
```

### Restart Services

```bash
docker-compose restart
```

### Rebuild From Scratch

```bash
docker-compose down -v  # Remove volumes (deletes database!)
docker-compose build --no-cache
./docker-start.sh
```

### Run Tests

```bash
docker-compose exec app php artisan test
```

## Project Structure in Docker

```
Host Machine (Your Computer)
├── All source code files (.php, .js, .vue, etc.)
├── .env file
└── Configuration files

Docker Container
├── Source code (mounted from host)
├── vendor/ (NOT synced from host, managed by Docker)
├── node_modules/ (NOT synced from host, managed by Docker)
└── ALL PHP extensions (intl, redis, pdo_pgsql, etc.)
```

## Why This Approach?

1. **Consistent Environment**: Everyone has the same PHP version and extensions
2. **No Local PHP Required**: Don't need to install PHP, extensions, or databases on your host
3. **Isolated Dependencies**: Vendor directory managed inside Docker, avoiding conflicts
4. **Production Parity**: Docker setup matches production environment

## Troubleshooting

### "ext-intl is missing"
You're running composer on your host. Use:
```bash
docker-compose exec app composer [command]
```

### "Cannot connect to database"
Database is at `db:5432` inside Docker, not `localhost`. The `.env` file is already configured correctly:
```
DB_HOST=db
DB_PORT=5432
```

### "Redis connection refused"
Redis is at `redis:6379` inside Docker. The `.env` file is already configured:
```
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_CLIENT=phpredis
```

### Port 80 already in use
Another service is using port 80. Either:
1. Stop that service
2. Or change docker-compose.yml nginx ports to `8080:80` and access at http://localhost:8080

## Development Workflow

1. **Code on your host** - Edit files in your IDE normally
2. **Run commands in Docker** - Always use `docker-compose exec app [command]`
3. **View in browser** - Access http://localhost
4. **Check logs** - Use `docker-compose logs -f` to debug

## Need Help?

```bash
# Check what's running
docker-compose ps

# Check app container health
docker-compose logs app

# Access app container shell
docker-compose exec app bash

# Inside the container, you can run anything:
# composer install, php artisan migrate, etc.
```
