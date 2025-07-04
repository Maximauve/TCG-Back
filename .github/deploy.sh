#!/bin/sh

composer install --no-dev --optimize-autoloader
if [ $? -ne 0 ]; then
    echo "Composer install failed"
    exit 1
fi
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
if [ $? -ne 0 ]; then
    echo "Migration failed"
    exit 1
fi
php bin/console cache:clear
php bin/console cache:pool:clear --all
