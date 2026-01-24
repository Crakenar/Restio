# Docker-Only Development Workflow

This guide shows you how to develop **without installing PHP extensions** (like Redis) on your local machine. Everything runs through Docker!

## 🚀 Quick Start

### Initial Setup

1. **Install dependencies through Docker**:
   ```bash
   ./composer-docker.sh install
   ```

2. **Start development**:
   ```bash
   ./docker-dev.sh
   ```

That's it! Your app is running at http://localhost:8000

## 📦 Helper Scripts

### `./composer-docker.sh` - Run Composer Through Docker

Run any Composer command through Docker instead of your local PHP:

```bash
./composer-docker.sh install          # Install dependencies
./composer-docker.sh update           # Update dependencies
./composer-docker.sh require pkg/name # Install new package
./composer-docker.sh dump-autoload    # Regenerate autoload files
```

**Why?** Your local PHP doesn't have Redis extension, but Docker does. This script runs Composer inside the Docker container where all extensions are available.

### `./docker-dev.sh` - Start Development Environment

Starts all development services:
- ✅ Laravel server (port 8000) - runs in Docker
- ✅ Queue worker - runs in Docker
- ✅ Log viewer (pail) - runs in Docker
- ✅ Vite dev server (port 5173) - runs on host for hot reload

**Usage:**
```bash
./docker-dev.sh
```

Press `Ctrl+C` to stop all services.

## 🛠️ Running Artisan Commands

### Option 1: Through Docker (Recommended)

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec app php artisan make:controller UserController
```

### Option 2: Create an Alias (Easier)

Add this to your `~/.bashrc` or `~/.zshrc`:

```bash
alias artisan='docker compose exec app php artisan'
```

Then reload your shell:
```bash
source ~/.bashrc  # or source ~/.zshrc
```

Now you can run:
```bash
artisan migrate
artisan tinker
artisan make:controller UserController
```

## 📋 Common Tasks

### Run Migrations
```bash
docker compose exec app php artisan migrate
```

### Clear Caches
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### Run Tests
```bash
docker compose exec app php artisan test
```

### Build Frontend Assets
```bash
npm run build  # Runs on host
```

### Access Database
```bash
docker compose exec db psql -U laravel -d laravel
```

### View Logs
```bash
docker compose logs -f app      # Application logs
docker compose logs -f horizon  # Queue logs
docker compose logs -f nginx    # Web server logs
```

## 🔄 Daily Workflow

### Starting Your Day
```bash
docker compose up -d      # Start containers
./docker-dev.sh           # Start dev environment
```

### During Development
```bash
# Add a new package
./composer-docker.sh require vendor/package

# Run migrations
docker compose exec app php artisan migrate

# Clear cache when needed
docker compose exec app php artisan cache:clear
```

### Ending Your Day
```bash
# Stop dev environment (Ctrl+C in docker-dev.sh terminal)
docker compose down       # Stop all containers
```

## 🎯 Why This Approach?

### ✅ Advantages
- No need to install PHP extensions locally
- Consistent environment across team members
- Works on any OS (Windows, Mac, Linux)
- Keeps your local machine clean
- Matches production environment exactly

### ❌ You Don't Need
- ❌ Local Redis extension
- ❌ Local PostgreSQL
- ❌ Local PHP extensions
- ❌ Matching PHP versions locally

### ✅ You Only Need
- ✅ Docker & Docker Compose
- ✅ Node.js & npm (for Vite)

## 🐛 Troubleshooting

### "Redis class not found" error

**Problem:** You're running commands with local PHP instead of Docker PHP.

**Solution:** Use `./composer-docker.sh` or `docker compose exec app`:
```bash
# ❌ Wrong
composer install

# ✅ Correct
./composer-docker.sh install
```

### Permission errors on files

**Problem:** Files created by Docker are owned by www-data.

**Solution:** Already fixed! The entrypoint script automatically sets correct ownership. If you still have issues:
```bash
docker compose down
docker compose up -d --build
```

### Container not running

**Problem:** Commands fail because containers aren't running.

**Solution:** Start containers first:
```bash
docker compose up -d
```

## 📚 Documentation Structure

- **SETUP_COMPLETE.md** - Overview of what's been set up
- **DOCKER_PERMISSIONS.md** - How permission fixing works
- **DOCKER_WORKFLOW.md** (this file) - Docker-only development workflow
- **DOCKER_SETUP.md** - Docker configuration details

## 🎉 Summary

You now have a complete Docker-based development environment where:

1. **All PHP runs in Docker** (with Redis extension)
2. **Vite runs on host** (for hot reload)
3. **Permissions are automatic** (no chown/chmod needed)
4. **Legal pages are live** (Terms, Privacy, GDPR)

Just run `./docker-dev.sh` and start coding! 🚀
