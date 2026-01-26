# Docker Setup Guide for Restio

## Quick Start

**Start everything with one command:**
```bash
./docker-start.sh
```

That's it! The script will:
- ✅ Build Docker images with ALL required PHP extensions (intl, redis, pdo_pgsql, etc.)
- ✅ Start all services (App, Nginx, PostgreSQL, Redis, Horizon)
- ✅ The container automatically:
  - Installs composer dependencies inside Docker
  - Waits for database to be ready
  - Runs migrations
  - Optimizes caches
  - Sets up storage links

## Access Your Application

- **Main App**: http://localhost
- **Admin Panel**: http://localhost/admin
  - Email: `admin@restio.com`
  - Password: `password`
- **Horizon (Queue Dashboard)**: http://localhost/horizon

## Common Commands

### Start Services
```bash
./docker-start.sh
# or manually:
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
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

### Run Artisan Commands
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app php artisan queue:work
```

### Run Composer
**IMPORTANT:** Always run composer commands inside Docker, never on your host machine.

```bash
# Install dependencies
docker-compose exec app composer install

# Add a new package
docker-compose exec app composer require package/name

# Update dependencies
docker-compose exec app composer update

# Remove a package
docker-compose exec app composer remove package/name
```

The `intl` extension and all other PHP extensions are available inside the Docker container.

### Run NPM Commands
```bash
docker-compose exec app npm install
docker-compose exec app npm run dev
docker-compose exec app npm run build
```

### Access Database
```bash
docker-compose exec db psql -U laravel -d laravel
```

### Access Redis CLI
```bash
docker-compose exec redis redis-cli
```

### Clear Caches
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Rebuild Everything (if issues)
```bash
docker-compose down -v  # Remove volumes
docker-compose build --no-cache  # Rebuild from scratch
./docker-start.sh
```

## Services

### App (PHP-FPM)
- PHP 8.3 with all extensions (including intl for Filament)
- Redis extension (phpredis)
- Runs Laravel application

### Nginx
- Web server
- Proxies requests to PHP-FPM
- Port 80

### PostgreSQL
- Database server
- Port 5432 (accessible from host)
- Data persisted in Docker volume

### Redis
- Cache and queue driver
- Port 6379 (accessible from host)
- Data persisted in Docker volume

### Horizon
- Queue worker and monitoring
- Automatically processes jobs

### Scheduler
- Runs Laravel scheduled tasks
- Runs `php artisan schedule:work`

## Environment Configuration

The `.env` file is configured for Docker with:
- `DB_HOST=db` (Docker service name)
- `REDIS_HOST=redis` (Docker service name)
- `REDIS_CLIENT=phpredis` (extension available in Docker)
- `APP_URL=http://localhost`

## Important Notes

- **Never run composer on your host machine** - always use `docker-compose exec app composer [command]`
- **Never run artisan on your host machine** - always use `docker-compose exec app php artisan [command]`
- The Docker container has ALL required PHP extensions (intl, redis, pdo_pgsql, gd, zip, etc.)
- Vendor directory is managed inside Docker, not synced from your host

## Switching Between Local and Docker

### Using Docker (Recommended)
```bash
# Already configured! Just run:
./docker-start.sh
```

### Using Local PHP (If Needed)
```bash
# 1. Restore local .env
cp .env.local.backup .env

# 2. Update to use localhost:
#    DB_HOST=127.0.0.1
#    REDIS_HOST=127.0.0.1
#    REDIS_CLIENT=predis (since no phpredis extension)

# 3. Start local services
php artisan serve
```

## Troubleshooting

### Port Already in Use
```bash
# Check what's using port 80
sudo lsof -i :80

# Or change the port in docker-compose.yml:
# nginx:
#   ports:
#     - "8080:80"  # Use port 8080 instead
```

### Permission Errors
```bash
docker-compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
```

### Database Connection Failed
```bash
# Check if database is running
docker-compose ps db

# Check database logs
docker-compose logs db

# Restart database
docker-compose restart db
```

### Redis Connection Failed
```bash
# Check if Redis is running
docker-compose ps redis

# Test Redis connection
docker-compose exec redis redis-cli ping

# Should return: PONG
```

### Filament Issues
The Docker image includes the `intl` extension required by Filament. If you see intl errors:
```bash
# Rebuild the image
docker-compose build --no-cache app
docker-compose up -d
```

## File Permissions

The Docker setup automatically handles file permissions using your host user ID. If you encounter permission issues:

1. Check your user ID:
```bash
id -u  # Should output 1000
id -g  # Should output 1000
```

2. Update `.env.docker` if different:
```bash
HOST_UID=your_uid
HOST_GID=your_gid
```

3. Rebuild:
```bash
docker-compose down
docker-compose build
./docker-start.sh
```

## Performance Tips

### Use Docker Volumes for Better Performance
Already configured in `docker-compose.yml`:
```yaml
volumes:
  - .:/var/www
  - /var/www/vendor  # Excludes vendor from sync
  - /var/www/node_modules  # Excludes node_modules from sync
```

### Optimize for Production
Use the production Dockerfile target:
```bash
docker-compose -f docker-compose.prod.yml up -d
```
