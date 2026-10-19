@echo off
echo ========================================
echo Excellence Academy - PHP Server
echo ========================================
echo.
echo Starting PHP built-in server...
echo.
echo Access your website at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.
cd /d %~dp0
php -S localhost:8000
pause
