# Database Maintenance Plan - El Diablo Restaurant

## Overview
This document outlines a simple but comprehensive database maintenance plan for El Diablo Restaurant reservation system running on MariaDB 10.11 in Docker.

## Maintenance Schedule

### Daily Tasks
- **Automated Backups**: Full database backup at 2:00 AM
- **Log Rotation**: Clean up old MariaDB logs
- **Health Check**: Verify database connectivity and performance

### Weekly Tasks (Sundays at 3:00 AM)
- **Data Cleanup**: Remove reservations older than 90 days
- **Index Optimization**: Rebuild fragmented indexes
- **Statistics Update**: Update table statistics for query optimization

### Monthly Tasks (1st of month at 4:00 AM)
- **Full Backup Verification**: Test backup restoration
- **Performance Review**: Analyze slow queries and optimize
- **Security Audit**: Review user permissions and access logs

## Implementation

### 1. Automated Backup Script

Create `scripts/backup-db.sh`:
```bash
#!/bin/bash

# Database Backup Script
# Usage: ./backup-db.sh

BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="lamaison_db_backup_${DATE}.sql"
RETENTION_DAYS=30

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Perform backup
docker exec unix_db mysqldump \
  --single-transaction \
  --routines \
  --triggers \
  --all-databases \
  --user=root \
  --password=$(cat /path/to/secrets/DB_PASSWORD_FILE) \
  > "$BACKUP_DIR/$BACKUP_FILE"

# Compress backup
gzip "$BACKUP_DIR/$BACKUP_FILE"

# Remove old backups (keep last 30 days)
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete

# Log backup completion
echo "$(date): Database backup completed - $BACKUP_FILE.gz" >> /var/log/db-maintenance.log
```

### 2. Data Cleanup Script

Create `scripts/cleanup-old-data.sh`:
```bash
#!/bin/bash

# Clean up old reservations (older than 90 days)
# Usage: ./cleanup-old-data.sh

RETENTION_DAYS=90

docker exec unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) -e "
DELETE FROM YOUR_DATABASE_NAME.reservations 
WHERE created_at < DATE_SUB(NOW(), INTERVAL $RETENTION_DAYS DAY);
"

# Log cleanup
echo "$(date): Cleaned up reservations older than $RETENTION_DAYS days" >> /var/log/db-maintenance.log
```

### 3. Database Optimization Script

Create `scripts/optimize-db.sh`:
```bash
#!/bin/bash

# Database optimization script
# Usage: ./optimize-db.sh

docker exec unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) -e "
-- Analyze tables for statistics
ANALYZE TABLE YOUR_DATABASE_NAME.reservations;
ANALYZE TABLE YOUR_DATABASE_NAME.users;

-- Optimize tables to reduce fragmentation
OPTIMIZE TABLE YOUR_DATABASE_NAME.reservations;
OPTIMIZE TABLE YOUR_DATABASE_NAME.users;

-- Check table health
CHECK TABLE YOUR_DATABASE_NAME.reservations;
CHECK TABLE YOUR_DATABASE_NAME.users;
"

echo "$(date): Database optimization completed" >> /var/log/db-maintenance.log
```

## Cron Jobs Setup

Add to crontab (`crontab -e`):

```bash
# Daily backup at 2:00 AM
0 2 * * * /path/to/scripts/backup-db.sh

# Weekly cleanup and optimization (Sunday 3:00 AM)
0 3 * * 0 /path/to/scripts/cleanup-old-data.sh
10 3 * * 0 /path/to/scripts/optimize-db.sh

# Monthly backup verification (1st of month 4:00 AM)
0 4 1 * * /path/to/scripts/verify-backup.sh
```

## Monitoring & Alerting

### 1. Health Check Script

Create `scripts/health-check.sh`:
```bash
#!/bin/bash

# Database health check
# Usage: ./health-check.sh

# Check if database is responding
if docker exec unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) -e "SELECT 1;" > /dev/null 2>&1; then
    echo "$(date): Database health check - OK" >> /var/log/db-maintenance.log
else
    echo "$(date): Database health check - FAILED" >> /var/log/db-maintenance.log
    # Send alert (configure your preferred alerting method)
    # mail -s "Database Health Check Failed" admin@restaurant.com
fi

# Check disk space
DISK_USAGE=$(df /var/lib/mysql | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): Disk usage warning: ${DISK_USAGE}%" >> /var/log/db-maintenance.log
fi
```

### 2. Performance Monitoring

Create `scripts/performance-check.sh`:
```bash
#!/bin/bash

# Performance monitoring script
# Usage: ./performance-check.sh

docker exec unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) -e "
-- Show slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;

-- Show connection status
SHOW STATUS LIKE 'Connections';
SHOW STATUS LIKE 'Threads_connected';
" >> /var/log/db-performance.log
```

## Backup Verification

### Backup Test Script

Create `scripts/verify-backup.sh`:
```bash
#!/bin/bash

# Test backup restoration
# Usage: ./verify-backup.sh

BACKUP_DIR="/backups"
LATEST_BACKUP=$(ls -t $BACKUP_DIR/*.sql.gz | head -1)

if [ -z "$LATEST_BACKUP" ]; then
    echo "$(date): No backup found for verification" >> /var/log/db-maintenance.log
    exit 1
fi

# Create test database
docker exec unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) -e "CREATE DATABASE IF NOT EXISTS test_backup;"

# Restore backup to test database
gunzip -c "$LATEST_BACKUP" | docker exec -i unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) test_backup

# Verify data integrity
RECORD_COUNT=$(docker exec unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) -e "SELECT COUNT(*) FROM test_backup.YOUR_DATABASE_NAME.reservations;" | tail -1)

# Clean up test database
docker exec unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) -e "DROP DATABASE test_backup;"

echo "$(date): Backup verification completed - $RECORD_COUNT records found" >> /var/log/db-maintenance.log
```

## Emergency Procedures

### 1. Database Recovery

**Quick Recovery from Backup:**
```bash
# Stop application
docker-compose stop web

# Restore latest backup
LATEST_BACKUP=$(ls -t /backups/*.sql.gz | head -1)
gunzip -c "$LATEST_BACKUP" | docker exec -i unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE)

# Restart application
docker-compose start web
```

### 2. Point-in-Time Recovery (if binary logs enabled)

```bash
# Find binary log position from backup time
mysqlbinlog --start-datetime="2024-01-01 00:00:00" /var/lib/mysql/mysql-bin.000001 > recovery.sql

# Apply binary logs
docker exec -i unix_db mysql -u root -p$(cat /path/to/secrets/DB_PASSWORD_FILE) < recovery.sql
```

## Directory Structure

```
Unix-Project2/
├── scripts/
│   ├── backup-db.sh
│   ├── cleanup-old-data.sh
│   ├── optimize-db.sh
│   ├── health-check.sh
│   ├── performance-check.sh
│   └── verify-backup.sh
├── backups/
│   └── (daily backup files)
└── logs/
    ├── db-maintenance.log
    └── db-performance.log
```

## Security Considerations

1. **File Permissions**: Ensure scripts have proper permissions (700)
2. **Password Security**: Store database credentials securely
3. **Backup Encryption**: Consider encrypting sensitive backup files
4. **Access Control**: Limit who can execute maintenance scripts

## Performance Tuning

### Recommended MariaDB Configuration

Add to `docker/mysql.cnf`:
```ini
[mysqld]
# General query and connection settings
max_connections = 100
query_cache_size = 64M
query_cache_type = 1

# InnoDB settings
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_method = O_DIRECT

# Binary logging for point-in-time recovery
log_bin = /var/lib/mysql/mysql-bin
binlog_format = ROW
expire_logs_days = 7

# Slow query logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

## Maintenance Checklist

### Daily
- [ ] Backup completed successfully
- [ ] Database is responsive
- [ ] Disk space is adequate (<80%)

### Weekly
- [ ] Old data cleaned up
- [ ] Database optimized
- [ ] Performance metrics reviewed

### Monthly
- [ ] Backup restoration tested
- [ ] Security audit completed
- [ ] Configuration reviewed

### Log Locations
- Maintenance logs: `/var/log/db-maintenance.log` 
- Performance logs: `/var/log/db-performance.log` 
- MariaDB logs: `docker logs unix_db` 
- Application logs: `docker logs unix_web` 

This maintenance plan ensures that our restaurant reservation database remains healthy, performant, and secure with minimal manual intervention.

## Environment Variables

Replace these placeholders with your actual values:
- `YOUR_DATABASE_NAME`: Your database name
- `YOUR_SECURE_PASSWORD`: Your database password
- `/path/to/secrets/DB_PASSWORD_FILE`: Path to your password file
- `/var/log/db-maintenance.log`: Your log file path
