#!/bin/bash

# Database health check script
# Usage: ./health-check.sh

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BACKUP_DIR="$PROJECT_ROOT/backups"
DB_PASSWORD_FILE="$PROJECT_ROOT/secrets/db_password.txt"

mkdir -p "$BACKUP_DIR"

if [ ! -f "$DB_PASSWORD_FILE" ]; then
    echo "$(date): ERROR - Database password file not found at $DB_PASSWORD_FILE"
    exit 1
fi

DB_PASSWORD=$(cat "$DB_PASSWORD_FILE")

# Check if database container is running
if ! docker ps | grep -q "unix_db"; then
    echo "$(date): ERROR - Database container is not running"
    echo "$(date): ERROR - Database container is not running" >> "$BACKUP_DIR/health.log"
    exit 1
fi

# Check if database is responding
if docker exec unix_db mysql -u root -p"$DB_PASSWORD" -e "SELECT 1;" > /dev/null 2>&1; then
    echo "$(date): Database health check - OK"
    echo "$(date): Database health check - OK" >> "$BACKUP_DIR/health.log"
    HEALTH_STATUS="OK"
else
    echo "$(date): Database health check - FAILED"
    echo "$(date): Database health check - FAILED" >> "$BACKUP_DIR/health.log"
    HEALTH_STATUS="FAILED"
fi

# Check disk space
DISK_USAGE=$(docker exec unix_db df /var/lib/mysql | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 80 ]; then
    echo "$(date): Disk usage warning: ${DISK_USAGE}%"
    echo "$(date): Disk usage warning: ${DISK_USAGE}%" >> "$BACKUP_DIR/health.log"
    DISK_STATUS="WARNING"
else
    DISK_STATUS="OK"
fi

# Check connection count
CONNECTIONS=$(docker exec unix_db mysql -u root -p"$DB_PASSWORD" -e "SHOW STATUS LIKE 'Threads_connected';" -s -N 2>/dev/null | awk '{print $2}')
echo "$(date): Current connections: $CONNECTIONS"
echo "$(date): Current connections: $CONNECTIONS" >> "$BACKUP_DIR/health.log"

# Check database size
DB_SIZE=$(docker exec unix_db mysql -u root -p"$DB_PASSWORD" -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema = 'unix_project';" -s -N 2>/dev/null)
echo "$(date): Database size: ${DB_SIZE}MB"
echo "$(date): Database size: ${DB_SIZE}MB" >> "$BACKUP_DIR/health.log"

# Summary
echo "$(date): Health Check Summary - DB: $HEALTH_STATUS, Disk: $DISK_STATUS, Connections: $CONNECTIONS, Size: ${DB_SIZE}MB"
echo "$(date): Health Check Summary - DB: $HEALTH_STATUS, Disk: $DISK_STATUS, Connections: $CONNECTIONS, Size: ${DB_SIZE}MB" >> "$BACKUP_DIR/health.log"

# Exit with error code if health check failed
if [ "$HEALTH_STATUS" = "FAILED" ]; then
    exit 1
fi
