# Full-Stack Symfony + Next.js Application

A complete full-stack application with Symfony backend API and Next.js frontend, containerized with Docker.

## TOTAL TIME SPENT: 
    - 8 to 9 houres little by little past 3 days and night
    - STARTED: 20th
    - FINISHED: 22nd

#### AI TOOLS USED
```
1. My OWN architectral setup using turborepo
    - 'composer create-project symfony/skeleton apps/api-php'
    - 'npx create-next-app@latest apps/web'

2. ChatGPT:
    - Documentation
    - Formating
    - Seed data: 'users', 'providers', 'services' and 'slots'

2. Lovable - Simple design just took the HTML and CSS

3. Kilo Code:
 - Latest Package installtion
 - Docker setup
 - github action setup
 - phpunit setup
 - code verification
 - seperation of concern

4. Base 44: 
  - Architecture verification 
  - Looked for bugs
  - Further improvments
  - Security check
```

## Features

- **Backend**: Symfony 7.3 with JWT authentication, Doctrine ORM, PostgreSQL database
- **Frontend**: Next.js with React, TypeScript, and Tailwind CSS
- **Authentication**: JWT-based API authentication with user registration/login
- **Database**: PostgreSQL 15 with Doctrine migrations
- **Containerization**: Docker Compose setup with PHP-FPM, Nginx, PostgreSQL, and Next.js

## Project Structure

```
├── apps/
│   ├── api-php/          # Symfony backend API
│   └── web/              # Next.js frontend
├── docker/
│   └── nginx.conf        # Nginx configuration
├── docker-compose.yml    # Docker Compose configuration
└── README.md
```

## Quick Start

### Prerequisites

- Docker and Docker Compose (latest versions recommended)
- Git
- At least 4GB RAM available for Docker

### Step-by-Step Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd harba
   ```

2. **Copy environment files**
   ```bash
   cp apps/api-php/.env.example apps/api-php/.env
   cp .env.example .env  # If you have root env file
   ```

3. **Review environment configuration** (optional)
   ```bash
   # Check the copied .env file - default values should work for local development
   cat apps/api-php/.env
   ```

4. **Start all services with Docker**
   ```bash
   # Build and start all containers (this may take several minutes on first run)
   docker compose up --build

   # Or run in background
   docker compose up --build -d
   ```

5. **Wait for services to be ready**
   ```bash
   # Check service status
   docker compose ps

   # View logs to ensure everything started correctly
   docker compose logs -f
   ```

6. **Seed the database** (automatic on first startup)
   ```bash
   # Database seeding happens automatically when DATABASE_SEED=true in .env
   # To manually seed/reseed the database:
   docker compose exec api-php php bin/console doctrine:fixtures:load --no-interaction
   ```

   **Test Credentials** (after seeding):
   - **Regular user**: `user@example.com` / `user123`
   - **Admin user**: `admin@example.com` / `admin123`

7. **Access the application**
   - **Frontend (Next.js)**: http://localhost:3000
   - **Backend API (Symfony)**: http://localhost:8080
   - **Database**: localhost:8001 (PostgreSQL)

### What to Expect

Once all services are running:
- **Frontend (localhost:3000)**: Modern React application with booking system
- **Backend API (localhost:8080)**: REST API with JWT authentication
- **Database**: PostgreSQL 15 with automatic migrations and seed data

### Running Tests

#### Backend Tests (PHP/Symfony)
```bash
# Run tests inside the PHP container
docker compose exec api-php php bin/phpunit

# Or run specific test files
docker compose exec api-php php bin/phpunit tests/Controller/BookingControllerTest.php

# Run with coverage (if needed)
docker compose exec api-php php bin/phpunit --coverage-html=var/coverage
```

#### Frontend Tests (Next.js/React)
```bash
# Run tests inside the web container
docker compose exec web npm test

# Or run tests in watch mode
docker compose exec web npm run test:watch

# Run tests with coverage
docker compose exec web npm test -- --coverage
```

#### Run All Tests
```bash
# Backend tests
docker compose exec api-php php bin/phpunit

# Frontend tests
docker compose exec web npm test
```

### Development Workflow

#### Making Code Changes
```bash
# The application uses volume mounting, so changes are reflected immediately
# Edit files in apps/api-php/ or apps/web/ and see changes live
```

#### Database Operations
```bash
# Access database directly
docker compose exec db psql -U postgres -d harba

# Run migrations manually (usually automatic)
docker compose exec api-php php bin/console doctrine:migrations:migrate

# Load/reload seed data
docker compose exec api-php php bin/console doctrine:fixtures:load --no-interaction

# Reset database and reseed
docker compose exec api-php php bin/console doctrine:database:drop --force
docker compose exec api-php php bin/console doctrine:database:create
docker compose exec api-php php bin/console doctrine:migrations:migrate
docker compose exec api-php php bin/console doctrine:fixtures:load --no-interaction
```

**Seeded Data Includes:**
- **Services**: Haircut (30 min), Massage (60 min), Facial (45 min), Consultation (30 min)
- **Providers**: John Doe, Jane Smith, Alex Johnson (with working hours)
- **Users**: 
  - Regular user: `user@example.com` / `user123`
  - Admin user: `admin@example.com` / `admin123`

#### Logs and Debugging
```bash
# View all logs
docker compose logs -f

# View specific service logs
docker compose logs -f api-php
docker compose logs -f web
docker compose logs -f db
```

### Verifying Your Setup

1. **Check all services are running:**
   ```bash
   docker compose ps
   # Should show 4 services: db, api-php, nginx, web all healthy
   ```

2. **Test API connectivity:**
   ```bash
   curl http://localhost:8080/api
   # Should return API documentation or welcome message
   ```

3. **Test frontend:**
   - Open http://localhost:3000 in browser
   - Should show the application homepage

4. **Test database connection:**
   ```bash
   docker compose exec api-php php bin/console doctrine:query:sql "SELECT 1"
   # Should return "1"
   ```

### Application Features

Once running, you can:

- **Register** a new user account
- **Login** with JWT authentication (or use seeded test accounts)
- **View available services** (haircut, massage, facial, consultation)
- **Check provider availability** and working hours
- **Book appointments** with time slot selection
- **View your bookings** with pagination
- **Cancel bookings** (if within policy)
- **Admin features**: View all bookings (admin users only)

**Quick Test:**
Login with seeded credentials:
- User: `user@example.com` / `user123`
- Admin: `admin@example.com` / `admin123`

### Troubleshooting

#### Common Issues

**Services not starting:**
```bash
# Check Docker resources
docker system df

# Restart with fresh build
docker compose down -v
docker compose up --build --force-recreate
```

**Database connection issues:**
```bash
# Check database logs
docker compose logs db

# Verify environment variables
docker compose exec api-php env | grep DATABASE
```

**Port conflicts:**
```bash
# Check what's using ports
lsof -i :3000
lsof -i :8080
lsof -i :8001

# Change ports in .env file if needed
```

**Tests failing:**
```bash
# Ensure database is seeded for tests
docker compose exec api-php php bin/console doctrine:fixtures:load --env=test

# Check test database configuration
docker compose exec api-php php bin/console doctrine:database:create --env=test
```

## API Documentation

### Authentication Endpoints

| Method | Endpoint | Description | Request Body |
|--------|----------|-------------|--------------|
| POST | `/api/register` | Register new user | `{"email": "string", "password": "string", "roles": ["ROLE_USER"]}` |
| POST | `/api/login_check` | Login and get JWT token | `{"username": "string", "password": "string"}` |

### Booking System Endpoints

| Method | Endpoint | Description | Headers |
|--------|----------|-------------|---------|
| GET | `/api/services` | Get all available services | `Authorization: Bearer <token>` |
| GET | `/api/providers` | Get all service providers | `Authorization: Bearer <token>` |
| GET | `/api/bookings/available-slots` | Get available time slots | `Authorization: Bearer <token>` |
| POST | `/api/bookings` | Create new booking | `Authorization: Bearer <token>` |
| GET | `/api/bookings/my` | Get user's bookings | `Authorization: Bearer <token>` |
| GET | `/api/bookings/all` | Get all bookings (admin) | `Authorization: Bearer <token>` |
| DELETE | `/api/bookings/{id}` | Cancel booking | `Authorization: Bearer <token>` |
| DELETE | `/api/bookings/{id}/hard-delete` | Permanently delete booking | `Authorization: Bearer <token>` |

### Other Protected Endpoints

| Method | Endpoint | Description | Headers |
|--------|----------|-------------|---------|
| GET | `/api/profile` | Get user profile | `Authorization: Bearer <token>` |

### Response Examples

**Registration Success:**
```json
{
  "message": "User registered successfully"
}
```

**Login Success:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

**Profile Response:**
```json
{
  "email": "user@example.com",
  "roles": ["ROLE_USER"]
}
```

**Services Response:**
```json
[
  {
    "id": 1,
    "name": "Haircut",
    "duration": 30
  },
  {
    "id": 2,
    "name": "Massage",
    "duration": 60
  }
]
```

**Available Slots Response:**
```json
[
  "2024-12-25 09:00:00",
  "2024-12-25 09:30:00",
  "2024-12-25 10:00:00"
]
```

**Booking Creation Success:**
```json
{
  "message": "Booking created"
}
```

**User Bookings Response:**
```json
{
  "data": [
    {
      "id": 1,
      "provider": "John Doe",
      "service": "Haircut",
      "datetime": "2024-12-25 10:00:00",
      "status": "confirmed"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 1,
    "pages": 1
  }
}
```

## Development

### Running without Docker

**Backend:**
```bash
cd apps/api-php
composer install
php bin/console doctrine:migrations:migrate
php bin/console server:start
```

**Frontend:**
```bash
cd apps/web
pnpm install
pnpm dev
```

### Database

The application uses PostgreSQL with Doctrine ORM. Migrations are automatically run on container startup.

### Environment Variables

Copy `apps/api-php/.env.example` to `apps/api-php/.env` and configure:

- `DATABASE_URL`: PostgreSQL connection string
- `JWT_SECRET_KEY`: Path to JWT private key
- `JWT_PUBLIC_KEY`: Path to JWT public key
- `JWT_PASSPHRASE`: JWT key passphrase

## Docker Services

- **db**: PostgreSQL 15 database
- **api-php**: PHP 8.4 FPM with Symfony
- **nginx**: Web server proxying to PHP-FPM
- **web**: Next.js production build

## Testing

### Automated Test Suites

#### Backend Tests (PHPUnit)
```bash
# Run all PHP tests
docker compose exec api-php php bin/phpunit

# Run with verbose output
docker compose exec api-php php bin/phpunit -v

# Run specific test class
docker compose exec api-php php bin/phpunit tests/Controller/BookingControllerTest.php

# Run tests with coverage report
docker compose exec api-php php bin/phpunit --coverage-html=var/coverage
```

#### Frontend Tests (Jest)
```bash
# Run all React tests
docker compose exec web npm test

# Run tests in watch mode (for development)
docker compose exec web npm run test:watch

# Run tests with coverage
docker compose exec web npm test -- --coverage
```

### Manual API Testing with cURL

#### 1. Register a new user
```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

#### 2. Login and get JWT token
```bash
# Using your registered user
curl -X POST http://localhost:8080/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"test@example.com","password":"password123"}'

# Or use seeded test user
curl -X POST http://localhost:8080/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"user@example.com","password":"user123"}'
```
Save the returned token for subsequent requests.

#### 3. Get user profile
```bash
curl -X GET http://localhost:8080/api/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### 4. Get available services
```bash
curl -X GET http://localhost:8080/api/services \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### 5. Get available providers
```bash
curl -X GET http://localhost:8080/api/providers \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### 6. Get available slots for a provider/service
```bash
curl -X GET "http://localhost:8080/api/bookings/available-slots?provider_id=1&service_id=1" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### 7. Book an appointment
```bash
curl -X POST http://localhost:8080/api/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "provider_id": 1,
    "service_id": 1,
    "datetime": "2024-12-25 10:00:00"
  }'
```

#### 8. View your bookings
```bash
curl -X GET "http://localhost:8080/api/bookings/my?page=1&limit=10" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### 9. Cancel a booking
```bash
curl -X DELETE http://localhost:8080/api/bookings/123 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### End-to-End Testing

1. **Start the application** as described in Quick Start
2. **Open browser** to http://localhost:3000
3. **Register** a new account
4. **Login** with your credentials
5. **Browse services and providers**
6. **Book an appointment** by selecting time slots
7. **View your bookings** in the dashboard
8. **Test booking cancellation** if needed

## Technologies Used

- **Backend**: Symfony 7.3, Doctrine ORM, LexikJWTAuthenticationBundle
- **Frontend**: Next.js 16, React 19, TypeScript, Styled Components
- **Database**: PostgreSQL 15
- **Containerization**: Docker, Docker Compose
- **Web Server**: Nginx
