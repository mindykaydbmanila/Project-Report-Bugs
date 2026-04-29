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

## Visual/UI Changes
- Before implementing visual effects (cursor effects, animations, themes), ask the user to describe the desired behavior in detail or request a reference image/video
- For UI element removal tasks, identify ALL matching elements first and confirm which specific one(s) to remove before editing

## Running Dev Servers
- On Windows, PHP is NOT in the bash PATH. Do not use `php artisan serve` directly in bash — use the full Windows path to php.exe OR run start.bat via `cmd /c start.bat`.
- Do NOT modify start.bat to hardcode PHP paths; keep the original `php artisan serve` command.
- If .bat files fail in bash with exit code 127, pivot immediately to launching backend and frontend directly rather than retrying the .bat.
- To kill servers on Windows, `taskkill /F /PID` often fails from the bash shell — ask the user to kill manually instead of retrying.

## Before Ending a Task
- After editing Vue/JS files, verify no duplicate `const` declarations or unclosed tags were introduced (a previous session crashed the dev server with a duplicate `const formatDate`).
- When modifying API base URLs or shared helpers like `apiBase`, verify all call sites still work — do not "simplify" without checking.
- After adding new backend routes/pages, remind the user a frontend/dev server restart may be needed to avoid 404s.

## Email & Queue
- This project uses queued mail delivery. If emails aren't sending, check that the queue worker (`php artisan queue:work`) is running before debugging SMTP/Mailtrap config.

## Tool Availability
- Playwright is installed globally as an MCP server — use it directly for browser automation without asking
- Do not refuse URL/browser tasks; use Playwright MCP or WebFetch as appropriate

## Verification Before Claiming Features Exist
- Verify plugin/marketplace/package existence (via npm, GitHub, or official docs) before proposing installation steps
- If something doesn't exist, say so immediately rather than proposing a workaround without asking
