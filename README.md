# Unix Project - Containerized Web Application

A containerized web application with three containers: Web server, Database server, and Cache server.

## Architecture

- **Web Server**: Nginx + PHP-FPM
- **Database**: MariaDB 10.11
- **Cache**: Redis 7

## Quick Start

1. Copy environment file:
   ```bash
   cp .env.example .env
   ```

2. Start the containers:
   ```bash
   docker-compose up -d
   ```

3. Access the application:
   - Web App: http://localhost:8080
   - Database: localhost:3306
   - Redis: localhost:6379

## Container Details

### Web Container
- **Port**: 8080
- **Technology**: Nginx + PHP 8
- **Features**: 
  - PHP-FPM for processing
  - MySQLi extension
  - Redis extension
  - Volume mount for live code updates

### Database Container
- **Port**: 3306
- **Technology**: MariaDB 10.11
- **Features**:
  - Persistent data storage
  - Auto-initialization with sample data
  - Custom database: `unix_project`

### Cache Container
- **Port**: 6379
- **Technology**: Redis 7
- **Features**:
  - Persistent data storage
  - Used for application caching

## Development

The application files are mounted as volumes, so changes to your code will be reflected immediately without rebuilding containers.

### Environment Variables

- `DB_HOST`: Database hostname (default: db)
- `DB_NAME`: Database name (default: unix_project)
- `DB_USER`: Database username (default: root)
- `DB_PASSWORD`: Database password (default: rootpass)
- `REDIS_HOST`: Redis hostname (default: redis)

## Stopping the Application

```bash
docker-compose down
```

## Database Schema

The database is automatically initialized with:
- `users` table - Sample user data
- `restaurant_reservations` table - Reservation system data

## Cache Usage

The application includes a Redis cache helper class in `php/cache.php` for easy caching operations.
