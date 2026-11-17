@echo off
echo ========================================================
echo Starting BaiStock - Storage and Inventory Management System
echo ========================================================
echo URL: http://localhost:8088
echo Admin Login:   admin / admin123
echo Owner Login:   owner / owner123
echo Staff Login:   staff / staff123
echo ========================================================
start http://localhost:8088/login
php -S localhost:8088 -t public
pause
