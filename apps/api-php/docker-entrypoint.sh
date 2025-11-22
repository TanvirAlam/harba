#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database..."
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
    sleep 2
done
echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Load fixtures only if DATABASE_SEED environment variable is set to true
if [ "${DATABASE_SEED:-false}" = "true" ]; then
    echo "Loading database fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction
fi

# Start php-fpm
echo "Starting PHP-FPM..."
exec php-fpm
