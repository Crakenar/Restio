#!/bin/bash
set -e

# Get host user ID and group ID from environment variables (defaults to www-data)
HOST_UID=${HOST_UID:-33}
HOST_GID=${HOST_GID:-33}

# Create a user/group that matches the host user's UID/GID if it doesn't exist
# Check if a group with the target GID exists
GROUP_NAME=$(getent group "$HOST_GID" | cut -d: -f1)
if [ -z "$GROUP_NAME" ]; then
    # Group doesn't exist, create it
    groupadd -g "$HOST_GID" hostgroup
    GROUP_NAME="hostgroup"
fi

# Check if a user with the target UID exists
USER_NAME=$(getent passwd "$HOST_UID" | cut -d: -f1)
if [ -z "$USER_NAME" ]; then
    # User doesn't exist, create it
    useradd -u "$HOST_UID" -g "$HOST_GID" -m -s /bin/bash hostuser 2>/dev/null || \
    useradd -u "$HOST_UID" -g "$HOST_GID" -M -N -s /bin/bash hostuser
    USER_NAME="hostuser"
fi

# Install composer dependencies if vendor doesn't exist (needed for all containers)
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Only run setup on app container (not horizon/scheduler)
if [ "$1" = "php-fpm" ]; then
    echo "Running initial setup..."

    # Wait for database to be ready
    echo "Waiting for database..."
    until php artisan db:show > /dev/null 2>&1; do
        echo "Database not ready, waiting..."
        sleep 2
    done
    echo "Database is ready!"

    # Run migrations
    echo "Running migrations..."
    php artisan migrate --force

    # Clear and cache config
    echo "Optimizing application..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    # Create storage link if it doesn't exist
    if [ ! -L "public/storage" ]; then
        php artisan storage:link
    fi
fi

# Fix ownership of storage and bootstrap/cache to match host user
echo "Fixing permissions for UID:GID = $HOST_UID:$HOST_GID"
chown -R "$HOST_UID:$HOST_GID" /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Ensure the log directory exists
mkdir -p /var/log/supervisor

# Configure PHP-FPM to run as host user
if [ "$1" = "php-fpm" ]; then
    echo "Configuring PHP-FPM to run as UID:GID = $HOST_UID:$HOST_GID"
    {
        echo "[www]"
        echo "user = $USER_NAME"
        echo "group = $GROUP_NAME"
    } > /usr/local/etc/php-fpm.d/zz-user.conf
fi

# Execute the main container command
exec "$@"
