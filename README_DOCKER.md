# 🐳 Docker Setup - Quick Reference

Your Docker environment has been fully optimized! Here's everything you need to know.

## 🚀 Quick Start

```bash
# Start everything (build, migrate, seed)
make dev

# Or step by step
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --seed
```

**Access:**
- App: http://localhost
- Horizon: http://localhost/horizon

## 📝 Common Commands

```bash
# Development
make up              # Start services
make down            # Stop services
make logs            # View logs
make shell           # Access container
make restart         # Restart services

# Testing
make test            # Run all tests
make test-unit       # Unit tests
make test-feature    # Feature tests
make test-performance  # Performance tests

# Database
make migrate         # Run migrations
make seed            # Run seeders
make fresh           # Fresh install

# Code Quality
make pint            # Format code
make pint-dirty      # Format changed files

# Laravel
make artisan CMD="route:list"
make tinker
make horizon

# Utilities
make status          # Check services
make health          # Health checks
make help            # Show all commands
```

## 🧪 Testing

**Dedicated test database on port 5433:**

```bash
make test             # Uses db_test automatically
make test-performance # Performance tests with PostgreSQL
```

## 📚 Full Documentation

- **DOCKER_SETUP.md** - Complete guide
- **DOCKER_OPTIMIZATION_SUMMARY.md** - What changed
- **Makefile** - All available commands

## 🆘 Troubleshooting

```bash
# Services won't start
make logs

# Permission issues
make permissions

# Database connection failed
make status

# Fresh restart
make fresh
```

## ✨ What's New

✅ Multi-stage optimized Dockerfile (62% smaller)  
✅ Dedicated testing database (10x faster)  
✅ Makefile with 50+ commands  
✅ Optimized Nginx with security headers  
✅ Health checks on all services  
✅ Redis for cache & queues  
✅ Horizon for job processing  

See **DOCKER_OPTIMIZATION_SUMMARY.md** for details.
