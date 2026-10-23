#!/bin/sh
set -eu

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    php artisan storage:link
fi
exec "$@"
