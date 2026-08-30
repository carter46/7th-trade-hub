#!/usr/bin/env bash
# Run from Laravel root AFTER a successful git pull (e.g. ~/domains/7th-tradehub.online/public_html)
set -euo pipefail

php artisan migrate --force
php artisan branding:sync-pwa
php artisan config:cache
php artisan route:cache

echo "Post-pull deploy steps finished."
