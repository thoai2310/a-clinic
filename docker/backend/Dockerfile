FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY be-s-clinic/ .

RUN if [ ! -d "vendor" ]; then composer install; fi

# Create necessary directories and log files
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/run \
    && touch /var/log/queue-worker.log \
    && touch /var/log/supervisord.log \
    && touch /var/log/nginx_stdout.log \
    && touch /var/log/nginx_stderr.log \
    && touch /var/log/php-fpm_stdout.log \
    && touch /var/log/php-fpm_stderr.log

# Copy configurations
COPY docker/backend/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/backend/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chown www-data:www-data /var/log/queue-worker.log \
    && chmod 755 /var/run

# Generate key if needed
RUN if grep -q "APP_KEY=$" .env; then php artisan key:generate --force; fi

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]