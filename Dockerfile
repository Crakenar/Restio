# =============================================================================
# Multi-stage optimized Dockerfile for Laravel
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Base PHP image with extensions
# -----------------------------------------------------------------------------
FROM php:8.3-fpm AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    supervisor \
    procps \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        pcntl \
        gd \
        bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# -----------------------------------------------------------------------------
# Stage 2: Dependencies (for better layer caching)
# -----------------------------------------------------------------------------
FROM base AS dependencies

# Copy only composer files
COPY composer.json composer.lock ./

# Install dependencies with optimizations
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-cache

# -----------------------------------------------------------------------------
# Stage 3: Development image (includes dev dependencies)
# -----------------------------------------------------------------------------
FROM base AS development

# Install development dependencies
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copy composer files
COPY composer.json composer.lock ./

# Install ALL dependencies (including dev)
RUN composer install \
    --no-scripts \
    --no-interaction \
    --prefer-dist

# Copy application files
COPY . .

# Copy supervisor configuration
COPY docker/supervisor/horizon.conf /etc/supervisor/conf.d/horizon.conf

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p /var/log/supervisor

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# -----------------------------------------------------------------------------
# Stage 4: Production image (optimized, no dev dependencies)
# -----------------------------------------------------------------------------
FROM base AS production

# Copy vendor from dependencies stage
COPY --from=dependencies /var/www/vendor /var/www/vendor

# Copy application files
COPY . .

# Copy supervisor configuration
COPY docker/supervisor/horizon.conf /etc/supervisor/conf.d/horizon.conf

# Run composer scripts (optimize autoloader, etc.)
RUN composer dump-autoload --optimize --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && chmod -R 755 public

# Optimize PHP-FPM configuration
RUN { \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 50'; \
    echo 'pm.start_servers = 10'; \
    echo 'pm.min_spare_servers = 5'; \
    echo 'pm.max_spare_servers = 15'; \
    echo 'pm.max_requests = 500'; \
} > /usr/local/etc/php-fpm.d/zz-docker.conf

# Security: Run as non-root user
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
