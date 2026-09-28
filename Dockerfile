FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci
COPY resources ./resources
RUN npm run build

FROM php:8.3-apache AS app
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install pdo_mysql zip gd && a2dismod -f mpm_event mpm_worker && a2enmod mpm_prefork rewrite && apache2ctl configtest && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader && chown -R www-data:www-data storage bootstrap/cache
COPY --from=assets /app/public/build /var/www/html/public/build
RUN chmod +x /var/www/html/docker/start-web.sh
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
EXPOSE 80
CMD ["/var/www/html/docker/start-web.sh"]
