# Docker Permissions Setup

This project includes automatic permission fixing for the Docker environment to prevent permission issues between the host and containers.

## How It Works

When you run `docker compose up`, the containers automatically:

1. Read your host user ID (UID) and group ID (GID) from environment variables
2. Set the correct ownership on `storage/` and `bootstrap/cache/` directories
3. Ensure you can run both Docker commands and local `composer run dev` without permission conflicts

## Configuration

The permission settings are configured in `.env.docker`:

```bash
# Host user/group IDs for proper file permissions
HOST_UID=1000
HOST_GID=1000
```

### Finding Your UID/GID

If you need to change these values (e.g., on a different machine), run:

```bash
id -u  # Your UID
id -g  # Your GID
```

Then update `.env.docker` with your values.

## What This Fixes

Before this setup, you might have encountered:

- ❌ Permission denied errors when running `composer run dev`
- ❌ Unable to write to `storage/logs/laravel.log`
- ❌ Unable to create cache files
- ❌ Files created by Docker being owned by `www-data` or `root`

Now:

- ✅ Docker and local PHP commands work seamlessly
- ✅ All files maintain correct ownership
- ✅ No manual `chmod` or `chown` needed
- ✅ Permissions are automatically fixed on container startup

## Usage

Just start your containers normally:

```bash
docker compose up -d
```

Or run local development:

```bash
composer run dev
```

Both will work without permission issues!

## Troubleshooting

If you still encounter permission issues:

1. **Verify your UID/GID in `.env.docker`**:
   ```bash
   grep HOST_ .env.docker
   ```

2. **Rebuild containers**:
   ```bash
   docker compose down
   docker compose up -d --build
   ```

3. **Check container logs**:
   ```bash
   docker compose logs app | grep "Fixing permissions"
   ```

   You should see: `Fixing permissions for UID:GID = 1000:1000`

4. **Manually fix current files** (if needed):
   ```bash
   docker compose exec -u root app chown -R 1000:1000 /var/www/storage /var/www/bootstrap/cache
   ```

## Technical Details

The permission fixing is handled by:

- **Entrypoint Script**: `docker/entrypoint.sh` - Runs before the main container command
- **Dockerfile**: Modified to use the entrypoint
- **docker-compose.yml**: Passes `HOST_UID` and `HOST_GID` environment variables
- **.env.docker**: Stores your user's UID/GID values
