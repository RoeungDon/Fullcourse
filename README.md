# Clone Fullstage Backend (ChatSystem)

Laravel API + Vue.js frontend + MySQL via Docker Compose.

## Prerequisites

- Docker Desktop (or Docker Engine + Compose v2)

## Quick start

```bash
# Fresh start (recommended first time / after DB credential changes)
docker compose down -v
docker compose up -d --build

# Status
docker compose ps

# Logs
docker compose logs -f
```

### Services / ports

| Service     | Container              | URL / port                    |
|-------------|------------------------|-------------------------------|
| Laravel API | `laravel-container`    | http://localhost:8000         |
| Vue (Vite)  | `vuejs-container`      | http://localhost:5173         |
| MySQL       | `mysql-container`      | host `localhost:3307` → 3306  |
| phpMyAdmin  | `phpmyadmin-container` | http://localhost:9000         |

### Database (matches `laravel-app/.env`)

- Database: `chat_system`
- User: `chat_user`
- Password: `chat_password`
- Root password: `root`

### Useful commands

```bash
# Shell into a running service (use SERVICE name, not container name)
docker compose exec laravel-service bash
docker compose exec vuejs-service sh

# Artisan
docker compose exec laravel-service php artisan migrate
docker compose exec laravel-service php artisan route:list

# API health
curl -s http://localhost:8000/up

# Stop
docker compose down

# Stop and wipe DB / node_modules volumes
docker compose down -v
```

### Common mistakes

- Service name is **`vuejs-service`**, not `vujs-service`.
- Use **service names** with `docker compose exec` (e.g. `vuejs-service`), not container names.

### Frontend env (`vuejs-app/.env`)

```
VITE_APP_API_URL=http://localhost:8000/api
VITE_APP_VERIFY_EMAIL_URL=http://localhost:5173/verify/email
VITE_APP_RESET_PASSWORD_URL=http://localhost:5173/reset-password
VITE_APP_GOOGLE_OAUTH_CALLBACK_URL=http://localhost:5173/google/oauth/callback
```

Mail is set to `log` in Laravel so auth emails write to `storage/logs` without real SMTP.
For Google OAuth, set real client id/secret in `laravel-app/.env`.
