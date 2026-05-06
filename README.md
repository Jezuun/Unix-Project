# El Diablo Restaurant

El Diablo Restaurant is a containerized restaurant reservation web app built with Nginx, PHP, MariaDB, and Redis. Customers can submit table reservations from the public website, and an admin user can log in to view reservations stored in the database.

## Features

- Public reservation page at `http://localhost:8080`
- Reservation form handled by PHP with server-side validation
- MariaDB database for users and reservations
- Admin login page at `http://localhost:8080/php/admin_login.php`
- Admin dashboard that displays reservation data from the database
- Redis-backed helper used for caching/rate limiting
- Docker Compose setup with three containers: web, database, and cache
- Maintenance scripts for database backup and health checks

## Technology Stack

- **Web server:** Nginx
- **Back end:** PHP with MySQLi
- **Database:** MariaDB 10.11
- **Cache:** Redis 7 Alpine
- **Frontend:** HTML, CSS, and JavaScript
- **Infrastructure:** Docker Compose

## Quick Start

### Prerequisites

- Docker Desktop or Docker Engine
- Docker Compose
- Git

### 1. Clone the project

```bash
git clone <repository-url>
cd Unix-Project2
```

### 2. Create the database password file

Each person running the project should create their own local password file:

```bash
mkdir -p secrets
cp secrets/db_password.example.txt secrets/db_password.txt
```

You can keep the example password or replace the contents of `secrets/db_password.txt` with your own password before the first startup.

Important: MariaDB stores its data in a Docker volume. If you change the password after the database has already been created, reset the database volume:

```bash
docker compose down -v
docker compose up -d --build
```

### 3. Start the application

```bash
docker compose up -d --build
```

### 4. Open the website

- Main website: `http://localhost:8080`
- Admin login: `http://localhost:8080/php/admin_login.php`

Default admin credentials:

- Username: `admin`
- Password: `password`

## How It Works

The public page is served from `website/website.html`. When a customer submits the reservation form, it posts to `website/php/index.php`, which validates the form and inserts the reservation into MariaDB.

The admin dashboard is served from `website/php/admin.php`. After login, it reads reservation records from the database and displays them in a table.

The database is local to the computer running Docker. If two people clone the project on two different computers, each person gets their own MariaDB container and their own local reservation data.

## Docker Containers

The project runs three containers:

- `unix_web`: Nginx and PHP web application
- `unix_db`: MariaDB database server
- `unix_redis`: Redis cache server

Ports:

- Website: `localhost:8080`
- MariaDB: `localhost:3306`
- Redis: `localhost:6379`

## Project Structure

```text
Unix-Project2/
├── docker/
│   ├── Dockerfile.web
│   ├── init.sql
│   └── nginx.conf
├── scripts/
│   ├── backup-db.sh
│   └── health-check.sh
├── secrets/
│   ├── db_password.example.txt
│   └── db_password.txt
├── website/
│   ├── php/
│   │   ├── admin.php
│   │   ├── admin_login.php
│   │   ├── cache.php
│   │   ├── connection.php
│   │   ├── csrf-protection.php
│   │   ├── index.php
│   │   ├── rate-limiter.php
│   │   ├── security-config.php
│   │   └── security-functions.php
│   ├── style.css
│   └── website.html
├── database-maintenance-template.md
├── docker-compose.yml
└── README.md
```

## Database

The database is named `unix_project`. It is initialized from `docker/init.sql` when the MariaDB container starts for the first time.

Tables:

- `users`: stores admin login information
- `reservations`: stores reservation form submissions

To open a database shell:

```bash
docker exec -it unix_db mysql -u root -p
```

Use the password from `secrets/db_password.txt`.

## Maintenance Scripts

The `scripts` folder contains project-relative scripts, so they work from any cloned location.

Run a database backup:

```bash
./scripts/backup-db.sh
```

Run a database health check:

```bash
./scripts/health-check.sh
```

Generated backup and log files are written to `backups/`, which is ignored by Git.

## Common Commands

Start or rebuild the app:

```bash
docker compose up -d --build
```

Stop the app:

```bash
docker compose down
```

View logs:

```bash
docker compose logs -f
```

Restart only the web container:

```bash
docker compose restart web
```

Reset the database and all saved reservations:

```bash
docker compose down -v
docker compose up -d --build
```

## Troubleshooting

If `http://localhost:8080` does not load, make sure Docker is running and the containers are started:

```bash
docker compose ps
```

If the reservation form shows a database connection error, check that the database container is running and that `secrets/db_password.txt` exists.

If Docker says the password secret file is missing, create it:

```bash
mkdir -p secrets
cp secrets/db_password.example.txt secrets/db_password.txt
```

If port `8080`, `3306`, or `6379` is already in use, stop the other service or change the port mapping in `docker-compose.yml`.

## Security Notes

- The real `secrets/db_password.txt` file should not be committed for a real deployment.
- The example file `secrets/db_password.example.txt` is safe to commit because it is only a template.
- Admin passwords are stored hashed in the database.
- SQL queries use prepared statements for user-submitted data.
- This project is intended for local class/demo use unless deployed with production-grade secrets, HTTPS, and hosting configuration.
