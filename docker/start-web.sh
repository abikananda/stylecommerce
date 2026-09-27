#!/bin/sh
set -eu
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link 2>/dev/null || true
exec apache2-foreground
