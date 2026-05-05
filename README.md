# El Diablo Restaurant - Containerized Web Application

A modern, Apple-styled restaurant reservation system built with PHP, Nginx, MariaDB, and Redis, all containerized with Docker.

## Features

- **Modern UI**: Apple-inspired design with glass morphism effects and dark theme
- **Reservation System**: Complete restaurant reservation management
- **Admin Dashboard**: Secure admin panel for viewing and managing reservations
- **Responsive Design**: Mobile-first approach with Apple-style interactions
- **Containerized Architecture**: Fully Dockerized with separate containers for web, database, and cache
- **Security**: Password hashing, secure session management, and Docker secrets

## Architecture

### Container Stack
- **Web Server**: Nginx + PHP 8.2-FPM
- **Database**: MariaDB 10.11
- **Cache**: Redis 7 (Alpine)

### Frontend Technologies
- **Styling**: Apple-inspired CSS with SF Pro font stack
- **Design System**: Glass morphism, backdrop blur, subtle animations
- **Responsive**: Mobile-optimized with proper breakpoints

### Backend Technologies
- **Language**: PHP 8.2
- **Database**: MySQLi with prepared statements
- **Caching**: Redis with helper class
- **Security**: Password hashing, session management

## Quick Start

### Prerequisites
- Docker and Docker Compose installed
- Git for cloning the repository

### Setup Steps

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd Unix-Project2
   ```

2. **Set up environment**:
   ```bash
   cp .env.example .env
   # Edit .env file if needed (defaults work out of the box)
   ```

3. **Start the application**:
   ```bash
   docker-compose up -d
   ```

4. **Access the application**:
   - **Main Website**: http://localhost:8080
   - **Admin Login**: http://localhost:8080/php/admin_login.php
   - **Database**: localhost:3306
   - **Redis**: localhost:6379

### Default Credentials
- **Admin Login**:
  - Username: `admin`
  - Password: `password`

## Project Structure

```
Unix-Project2/
├── docker/                 # Docker configuration files
│   ├── Dockerfile         # Web server container
│   ├── Dockerfile.web     # Web application container
│   ├── nginx.conf         # Nginx configuration
│   └── init.sql           # Database initialization script
├── website/               # Frontend application files
│   ├── php/              # PHP backend files
│   │   ├── admin.php     # Admin dashboard
│   │   ├── admin_login.php # Admin login
│   │   ├── connection.php # Database connection
│   │   ├── cache.php     # Redis cache helper
│   │   └── index.php     # Reservation form handler
│   ├── website.html      # Main landing page
│   └── style.css         # Apple-inspired styling
├── js/                   # JavaScript files
├── img/                  # Image assets
├── secrets/              # Docker secrets
├── docker-compose.yml     # Container orchestration
└── package.json          # Node.js dependencies (webpack)
```

## Container Details

### Web Container (`unix_web`)
- **Port**: 8080 (external) → 80 (internal)
- **Technology**: Nginx + PHP 8.2-FPM
- **Features**:
  - PHP-FPM for optimized PHP processing
  - MySQLi and Redis extensions
  - Volume mount for live code updates
  - Nginx reverse proxy configuration

### Database Container (`unix_db`)
- **Port**: 3306 (external) → 3306 (internal)
- **Technology**: MariaDB 10.11
- **Features**:
  - Persistent data storage (`db_data` volume)
  - Auto-initialization with `init.sql`
  - Custom database: `unix_project`
  - Secure password management via Docker secrets

### Cache Container (`unix_redis`)
- **Port**: 6379 (external) → 6379 (internal)
- **Technology**: Redis 7 Alpine
- **Features**:
  - Persistent data storage (`redis_data` volume)
  - Used for application caching
  - Lightweight and optimized

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Reservations Table
```sql
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    guests INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Development

### Live Development
The application files are mounted as volumes, so changes to your code will be reflected immediately without rebuilding containers.

### Frontend Development
For frontend development with hot reload:
```bash
npm install
npm start    # Development server with hot reload
npm run build    # Production build
```

### Environment Variables
The application uses the following environment variables (configured in `.env`):
- `DB_HOST`: Database hostname (default: `db`)
- `DB_NAME`: Database name (default: `unix_project`)
- `DB_USER`: Database username (default: `root`)
- `DB_PASSWORD`: Database password (from Docker secrets)
- `REDIS_HOST`: Redis hostname (default: `redis`)

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: Prepared statements with MySQLi
- **Session Management**: Secure PHP sessions for admin authentication
- **Docker Secrets**: Sensitive data (passwords) managed via Docker secrets
- **Input Validation**: Server-side validation for all user inputs

## Design System

### Apple-Inspired Styling
- **Typography**: SF Pro Display font stack
- **Color Palette**: Apple's dark theme with #0071e3 primary color
- **Components**: Glass morphism effects with backdrop blur
- **Animations**: Smooth transitions with cubic-bezier easing
- **Responsive**: Mobile-first design with proper breakpoints

### CSS Architecture
- **Modular**: Organized by component sections
- **Variables**: Consistent color and spacing values
- **Responsive**: Mobile-first media queries
- **Performance**: Optimized transitions and animations

## Cache Usage

The application includes a Redis cache helper class in `php/cache.php`:

```php
// Example usage
require_once 'cache.php';
$cache = new Cache();

// Set cache
$cache->set('key', $data, 3600); // 1 hour TTL

// Get cache
$data = $cache->get('key');

// Delete cache
$cache->delete('key');
```

## Management Commands

### Start the Application
```bash
docker-compose up -d
```

### View Logs
```bash
docker-compose logs -f          # All services
docker-compose logs -f web      # Web service only
docker-compose logs -f db       # Database only
docker-compose logs -f redis    # Redis only
```

### Stop the Application
```bash
docker-compose down              # Stop and remove containers
docker-compose down -v          # Stop, remove, and delete volumes
```

### Rebuild Containers
```bash
docker-compose build --no-cache  # Force rebuild without cache
docker-compose up -d --build     # Rebuild and start
```

## Monitoring

### Container Status
```bash
docker-compose ps               # Check container status
docker stats                    # Resource usage
```

### Database Access
```bash
# Connect to database container
docker exec -it unix_db mysql -u root -p

# Or use external tool
# Host: localhost
# Port: 3306
# Database: unix_project
# Username: root
# Password: (check secrets/db_password.txt)
```

## Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 8080, 3306, and 6379 are available
2. **Permission issues**: Check Docker permissions and volume mounts
3. **Database connection**: Verify database container is running
4. **Cache issues**: Check Redis container status

### Reset Application
```bash
# Complete reset (removes all data)
docker-compose down -v
docker system prune -f
docker-compose up -d
```
