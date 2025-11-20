# Full-Stack Symfony + Next.js Application

A complete full-stack application with Symfony backend API and Next.js frontend, containerized with Docker.

## Features

- **Backend**: Symfony 7.3 with JWT authentication, Doctrine ORM, MySQL database
- **Frontend**: Next.js with React, TypeScript, and Tailwind CSS
- **Authentication**: JWT-based API authentication with user registration/login
- **Database**: MySQL 8.0 with Doctrine migrations
- **Containerization**: Docker Compose setup with PHP-FPM, Nginx, MySQL, and Next.js

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

- Docker and Docker Compose
- Git

### Setup and Run

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd harba
   ```

2. **Copy environment files**
   ```bash
   cp apps/api-php/.env.example apps/api-php/.env
   ```

3. **Generate JWT keys** (optional - keys are already generated)
   ```bash
   cd apps/api-php
   php bin/console lexik:jwt:generate-keypair
   cd ../..
   ```

4. **Start all services**
   ```bash
   docker compose up --build
   ```

The application will be available at:
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8080

## API Documentation

### Authentication Endpoints

| Method | Endpoint | Description | Request Body |
|--------|----------|-------------|--------------|
| POST | `/api/register` | Register new user | `{"email": "string", "password": "string", "roles": ["ROLE_USER"]}` |
| POST | `/api/login_check` | Login and get JWT token | `{"username": "string", "password": "string"}` |

### Protected Endpoints

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

The application uses MySQL with Doctrine ORM. Migrations are automatically run on container startup.

### Environment Variables

Copy `apps/api-php/.env.example` to `apps/api-php/.env` and configure:

- `DATABASE_URL`: MySQL connection string
- `JWT_SECRET_KEY`: Path to JWT private key
- `JWT_PUBLIC_KEY`: Path to JWT public key
- `JWT_PASSPHRASE`: JWT key passphrase

## Docker Services

- **db**: MySQL 8.0 database
- **api-php**: PHP 8.4 FPM with Symfony
- **nginx**: Web server proxying to PHP-FPM
- **web**: Next.js production build

## Testing

### API Testing with cURL

**Register:**
```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

**Login:**
```bash
curl -X POST http://localhost:8080/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"test@example.com","password":"password123"}'
```

**Get Profile:**
```bash
curl -X GET http://localhost:8080/api/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## Technologies Used

- **Backend**: Symfony 7.3, Doctrine ORM, LexikJWTAuthenticationBundle
- **Frontend**: Next.js 16, React 19, TypeScript, Tailwind CSS
- **Database**: MySQL 8.0
- **Containerization**: Docker, Docker Compose
- **Web Server**: Nginx
