# Futsal API

A RESTful API for managing futsal teams, players, matches, and standings. The project uses Laravel 10 with PostgreSQL 17 and provides Swagger (OpenAPI) documentation.

## Features

- Authentication with Laravel Passport (Bearer token)
- CRUD for teams, players, matches, and team classification
- Swagger UI for API documentation
- Docker Compose setup with Postgres 17
- Seeders with Faker for sample data
- Health check endpoint

## Tech Stack

- PHP 8.3 / Laravel 10
- PostgreSQL 17
- Docker + Docker Compose
- L5-Swagger (OpenAPI)

## Quick Start (Docker Compose)

1) Build and start containers:

```bash
docker compose up -d --build app
```

2) The API will be available at:

- Base URL: http://localhost:8000/api
- Swagger UI: http://localhost:8000/api/documentation

> The container entrypoint runs migrations and seeders automatically by default.

### Optional entrypoint toggles

If you want to disable automatic migrations or seeders, set environment variables in `docker-compose.yml`:

- `RUN_MIGRATIONS=false`
- `RUN_SEED=false`

## Local Setup (without Docker)

### Requirements

- PHP 8.3
- Composer
- PostgreSQL 17

### Steps

1) Install dependencies:

```bash
composer install
```

2) Create your `.env` file:

```bash
cp .env.example .env
```

3) Configure database in `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=futsal_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

4) Generate app key:

```bash
php artisan key:generate
```

5) Run migrations and seeders:

```bash
php artisan migrate --seed
```

6) Passport keys and personal client (for login tokens):

```bash
php artisan passport:keys
php artisan passport:client --personal --name="Personal Access Client"
```

7) Generate Swagger docs:

```bash
php artisan l5-swagger:generate
```

8) Run the API server:

```bash
php artisan serve
```

- Base URL: http://127.0.0.1:8000/api
- Swagger UI: http://127.0.0.1:8000/api/documentation

## Authentication

- Login endpoint returns a bearer token.
- Most endpoints require `Authorization: Bearer <token>`.

**Seeded admin user (dev only):**

```
email: admin@admin.com
password: 123456
```

## API Endpoints

Base path: `/api`

- `GET /health` - health check
- `POST /login` - login and get token
- `POST /user` - get current user (auth required)
- `POST /logout` - logout (auth required)

Players:
- `GET /player`
- `GET /player/{id}`
- `POST /player`
- `PUT /player/{id}`
- `DELETE /player/{id}`

Teams:
- `GET /team`
- `GET /team/{id}`
- `POST /team`
- `PUT /team/{id}`
- `DELETE /team/{id}`

Matches:
- `GET /teamMatch`
- `GET /teamMatch/{id}`
- `POST /teamMatch`
- `PUT /teamMatch/{id}`
- `DELETE /teamMatch/{id}`

Classification:
- `GET /teamClassification`
- `GET /teamClassification/{id}`
- `POST /teamClassification`
- `PUT /teamClassification/{id}`
- `DELETE /teamClassification/{id}`

Pagination:
- List endpoints accept `?per_page=15` (min 1, max 100) and `?page=1`.

## Example Requests

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@admin.com","password":"123456"}'
```

### Create Team

```bash
curl -X POST http://localhost:8000/api/team \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{"name":"Tigers FC"}'
```

### Create Match

```bash
curl -X POST http://localhost:8000/api/teamMatch \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "date_team_match":"2026-02-10",
    "start_time":"18:00",
    "end_time":"19:00",
    "scoreboard": 0
  }'
```

### Update Team (requires match id)

```bash
curl -X PUT http://localhost:8000/api/team/<TEAM_ID> \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "name":"Tigers FC",
    "team_match_id":"<MATCH_ID>"
  }'
```

### Create Player

```bash
curl -X POST http://localhost:8000/api/player \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "name":"John Doe",
    "jersey_number": 10,
    "team_id":"<TEAM_ID>"
  }'
```

### Create Team Classification

```bash
curl -X POST http://localhost:8000/api/teamClassification \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "team_id":"<TEAM_ID>",
    "points": 20,
    "number_of_goals": 45
  }'
```

## Swagger

- UI: http://localhost:8000/api/documentation
- JSON: http://localhost:8000/docs?api-docs.json

## Troubleshooting

- If you change PHP code, rebuild the container:

```bash
docker compose up -d --build app
```

- View container logs:

```bash
docker compose logs -f app
```
