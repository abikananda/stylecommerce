#!/bin/sh
set -eu
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link 2>/dev/null || true
# Select the PHP Apache image's prefork MPM in the running container.
a2dismod -f mpm_event mpm_worker
a2enmod mpm_prefork
apache2ctl configtest
exec apache2-foreground
