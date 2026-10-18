#!/bin/sh
# ops/backup.sh — back up the live site: the database plus every uploaded image.
# Run on the server from the project folder:   sh ops/backup.sh
# Each run writes a dated folder under backups/ and keeps the newest 14.
# To run it nightly at 3am, add this line with `crontab -e` (adjust the path):
#   0 3 * * * cd /home/you/thrysus_project_db && sh ops/backup.sh >> backups/backup.log 2>&1
set -e

COMPOSE="docker compose -f docker-compose.prod.yml"
STAMP=$(date +%Y-%m-%d_%H%M)
DEST="backups/$STAMP"
mkdir -p "$DEST"

# database: a full SQL dump, using the root password from .env inside the db container
$COMPOSE exec -T db sh -c 'mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction thrysus_project_db' \
    | gzip > "$DEST/database.sql.gz"

# images: everything in the uploads volume, packed from inside the web container
$COMPOSE exec -T web tar -czf - -C /var/www/html images > "$DEST/images.tar.gz"

echo "Backed up to $DEST"

# keep only the 14 newest backups
ls -1d backups/20* 2>/dev/null | sort | head -n -14 | xargs -r rm -rf
