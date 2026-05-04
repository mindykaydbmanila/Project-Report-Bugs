# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

QA Bug Tracker — a full-stack app with a **Laravel 13** backend API and a **Nuxt 4 / Vue 3** frontend.

## Starting the App

```bat
start.bat
```
Launches both servers in separate console windows: backend on `:8000`, frontend on `:3000`.

## Backend (Laravel 13 — `backend/`)

```bash
# Install & set up from scratch
composer run setup       # installs deps, creates .env, generates key, runs migrations

# Development
composer run dev         # runs artisan serve + queue listener + pail logs + vite (concurrently)
php artisan serve        # API only, port 8000

# Migrations
php artisan migrate

# Testing
composer run test        # clears config cache, then runs PHPUnit
php artisan test         # equivalent

# Code style
./vendor/bin/pint        # Laravel Pint linter/formatter
```

## Frontend (Nuxt 4 — `frontend/`)

```bash
npm run dev              # dev server on localhost:3000
npm run build            # production build
npm run preview          # preview production build
```

## Architecture

### Data Flow
1. Frontend (`$fetch`) → `http://localhost:8000/api`
2. `BugController` validates, processes, stores
3. Images saved to `backend/storage/app/public/bug-images/`
4. SQLite persists all records

### API Endpoints (`backend/routes/api.php`)
| Method | Path | Purpose |
|--------|------|---------|
| GET | `/api/bugs` | List bugs (filterable by status, priority, scenario_type, search) |
| POST | `/api/bugs` | Create bug (multipart/form-data for image uploads) |
| GET | `/api/bugs/{id}` | Get single bug |
| PUT | `/api/bugs/{id}` | Update bug |
| DELETE | `/api/bugs/{id}` | Delete bug |
| GET | `/api/bugs-summary` | Aggregate stats for dashboard cards |

### Bug Model Fields
- `sequence`, `title`, `description`
- `priority`: `Critical | High | Medium | Low`
- `scenario_type`: `UI | Functionality | UI & Functionality`
- `status`: `Pending | Out of Scope | Ongoing | Completed`
- `images`: JSON array of storage URLs

### Frontend Structure
The entire UI lives in `frontend/app/pages/index.vue` (single-page app). It contains:
- Summary stat cards (calls `/api/bugs-summary`)
- Filterable/searchable bug table
- Create/Edit modal with multi-image upload (max 5 MB each)
- Delete confirmation and image viewer modals

Global styles and the design token system are in `frontend/assets/css/main.css`.

## Key Config
- **Database**: SQLite (`backend/database/database.sqlite`), no external DB needed
- **Frontend API base URL**: set in `frontend/nuxt.config.js` → `runtimeConfig.public.apiBase`
- **Test DB**: in-memory SQLite (configured in `backend/phpunit.xml`)
