# RESTful API

Laravel 13 REST API with Passport authentication (users CRUD).

## Requirements
- PHP 8.3+, Composer

## Installation
```bash
composer install
cp .env.example .env
php artisan key:generate
# set the database connection in .env (SQLite by default: touch database/database.sqlite)
php artisan migrate:fresh --seed
php artisan passport:keys
php artisan passport:client --personal --name=Personal --provider=users
php artisan serve
```

## Docker
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
| GET | `/users` | yes | Paginated list (8 per page) |
| POST | `/user/create` | yes | Create a user |
| POST | `/user/{id}` | yes | Show a user |
| PUT | `/user/{id}` | yes | Update a user |
| DELETE | `/user/{id}` | yes | Delete a user |

Send `Accept: application/json` and `Authorization: Bearer <token>` on protected routes.
Seeded users have the password `password`.
