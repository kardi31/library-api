#!/bin/sh
set -e

if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
fi

echo "Oczekiwanie na bazę danych..."
until nc -z db 5432; do
    sleep 1
done

echo "Uruchamianie migracji..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Ładowanie danych demonstracyjnych..."
php bin/console doctrine:fixtures:load --no-interaction

exec "$@"
