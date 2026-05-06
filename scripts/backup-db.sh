#!/bin/bash

# Database Backup Script for El Diablo Restaurant
# Usage: ./backup-db.sh

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BACKUP_DIR="$PROJECT_ROOT/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="el_diablo_db_backup_${DATE}.sql"
RETENTION_DAYS=30

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Get database password from secrets file
DB_PASSWORD_FILE="$PROJECT_ROOT/secrets/db_password.txt"

if [ ! -f "$DB_PASSWORD_FILE" ]; then
    echo "$(date): ERROR - Database password file not found at $DB_PASSWORD_FILE"
    exit 1
fi

DB_PASSWORD=$(cat "$DB_PASSWORD_FILE")

# Check if database container is running
if ! docker ps | grep -q "unix_db"; then
    echo "$(date): ERROR - Database container is not running"
    exit 1
fi

# Perform backup
echo "$(date): Starting database backup..."
docker exec unix_db mysqldump \
  --single-transaction \
  --routines \
  --triggers \
  --all-databases \
  --user=root \
  --password="$DB_PASSWORD" \
  > "$BACKUP_DIR/$BACKUP_FILE" 2>/dev/null

# Check if backup was successful
if [ $? -eq 0 ]; then
    # Compress backup
    gzip "$BACKUP_DIR/$BACKUP_FILE"
    
    # Remove old backups (keep last 30 days)
    find "$BACKUP_DIR" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
    
    # Log backup completion
    echo "$(date): Database backup completed - ${BACKUP_FILE}.gz"
    echo "$(date): Database backup completed - ${BACKUP_FILE}.gz" >> "$BACKUP_DIR/backup.log"
else
    echo "$(date): ERROR - Database backup failed"
    echo "$(date): ERROR - Database backup failed" >> "$BACKUP_DIR/backup.log"
    exit 1
fi
