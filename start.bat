@echo off
echo Starting QA Bug Tracker...
echo.

echo [1/2] Starting Laravel backend on http://localhost:8000
start "Laravel Backend" cmd /k "cd /d "%~dp0backend" && php artisan serve"

timeout /t 2 /nobreak >nul

echo [2/2] Starting Nuxt frontend on http://localhost:3000
start "Nuxt Frontend" cmd /k "cd /d "%~dp0frontend" && npm run dev"

echo.
echo Both servers are starting. Open http://localhost:3000 in your browser.
pause
