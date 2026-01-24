# Docker "Pull Access Denied" Fix

## Problem
```
Warning: pull access denied for restio-app, repository does not exist
```

## Cause
Docker Compose was trying to **pull** the image from Docker Hub instead of **building** it locally.

## ✅ Fixed
The docker-compose.yml has been updated to:
1. Build the image locally first
2. Tag it as `restio-app:latest`
3. Reuse the built image for horizon/scheduler

## 🚀 How to Start Now

### Option 1: Using Makefile (Recommended)
```bash
make build
make up
```

### Option 2: Manual Commands
```bash
# Build the image first
docker compose build

# Then start services
docker compose up -d
```

### Option 3: One Command
```bash
# Build and start in one go
docker compose up -d --build
```

## ✅ Verification

```bash
# Check all services are running
docker compose ps

# Should see:
# - app (running)
# - nginx (running)
# - db (running)
# - db_test (running)
# - redis (running)
# - horizon (running)
# - scheduler (running)
```

## 🎯 Quick Commands

```bash
make build    # Build images
make up       # Start services (builds if needed)
make down     # Stop services
make restart  # Restart everything
make logs     # View logs
```

## 📝 What Changed

**Before:**
```yaml
app:
  image: restio-app  # ❌ Tries to pull
  build: ...
```

**After:**
```yaml
app:
  build: ...  # ✅ Build first
  image: restio-app:latest  # Then tag locally
```

Now horizon and scheduler depend on `app` being built first.

## 💡 Future Builds

After the first build, subsequent starts will be faster:

```bash
# First time (builds everything)
make up  # ~3-4 minutes

# Subsequent times (uses cache)
make up  # ~10-20 seconds
```

## 🆘 Still Having Issues?

```bash
# Clean everything and rebuild
make clean
docker compose down -v
docker compose build --no-cache
docker compose up -d
```

---

**Status:** ✅ Fixed - Ready to use!
