# RESTful API

A small REST API built with Laravel 13 and Laravel Passport: token authentication plus CRUD for users.

## Stack
- Laravel 13, PHP 8.3+
- Laravel Passport (personal access tokens)
- SQLite by default, PostgreSQL in Docker

## Installation

### Local
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # or point DB_* in .env at another database
php artisan migrate:fresh --seed    # seeds 30 users, password: "password"
php artisan passport:keys
php artisan passport:client --personal --name=Personal --provider=users
php artisan serve                   # http://localhost:8000/api
```

### Docker
```bash
docker compose up -d --build        # API at http://localhost:8000/api
SEED=true docker compose up -d      # optional: seed 30 users on first start
```
The entrypoint runs the migrations, generates the Passport keys and the personal access client, and creates an `APP_KEY` if none is set. App storage (keys, logs) and Postgres data live in named volumes; `docker compose down -v` removes them. Override the port or database password with `APP_PORT` and `DB_PASSWORD`.

## Endpoints (prefix `/api`)
| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/login` | no | Returns a Bearer token |
| POST | `/signup` | no | Register a user |
| GET | `/logout` | yes | Revoke the current token |
| GET | `/user` | yes | Current user |
| GET | `/users` | yes | Paginated list (8 per page, `?page=n`) |
| POST | `/user/create` | yes | Create a user |
| POST | `/user/{id}` | yes | Show a user |
| PUT | `/user/{id}` | yes | Update a user |
| DELETE | `/user/{id}` | yes | Delete a user |

Send `Accept: application/json` on every request and `Authorization: Bearer <token>` on protected routes.

### Request fields
| Endpoint | Fields |
|---|---|
| `/login` | `email`, `password` (required), `remember_me` (boolean, token valid for 1 week) |
| `/signup`, `/user/create` | `name`, `email` (unique), `password` (all required) |
| `PUT /user/{id}` | `name`, `email`, `password` (all optional) |

### Example
```bash
# log in
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -d "email=neo@example.com&password=secret12"

# call a protected route
curl http://localhost:8000/api/users \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <access_token>"
```

Login response:
```json
{
  "data": { "id": 1, "name": "Neo", "email": "neo@example.com" },
  "access_token": "eyJ0eXAi...",
  "token_type": "Bearer",
  "expires_at": null
}
```

### Status codes
| Code | Meaning |
|---|---|
| 200 | Success |
| 201 | User created |
| 202 | User updated or deleted |
| 401 | Wrong credentials or missing/invalid token |
| 403 | Delete requested for a user that does not exist |
| 404 | User not found (show, update) |
| 422 | Validation failed |
