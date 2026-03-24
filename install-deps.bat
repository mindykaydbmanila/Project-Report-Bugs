@echo off
cd /d "%~dp0backend"
echo Installing laravel/socialite...
composer require laravel/socialite
echo.
echo Running migrations...
C:\php\php.exe artisan migrate
echo.
echo Done! Press any key to close.
pause
