#!/bin/bash
set -e

# Get host user ID and group ID from environment variables (defaults to www-data)
HOST_UID=${HOST_UID:-33}
HOST_GID=${HOST_GID:-33}

# Create a user/group that matches the host user's UID/GID if it doesn't exist
if ! getent group hostgroup > /dev/null 2>&1; then
    groupadd -g "$HOST_GID" hostgroup
fi

if ! id -u hostuser > /dev/null 2>&1; then
    useradd -u "$HOST_UID" -g "$HOST_GID" -m hostuser
fi

# Fix ownership of storage and bootstrap/cache to match host user
echo "Fixing permissions for UID:GID = $HOST_UID:$HOST_GID"
chown -R "$HOST_UID:$HOST_GID" /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Ensure the log directory exists
mkdir -p /var/log/supervisor

# Execute the main container command
exec "$@"
