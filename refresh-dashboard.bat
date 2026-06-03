@echo off
echo ========================================
echo PrimeChem Dashboard Refresh Script
echo ========================================
echo.

echo [1/4] Building Vue assets...
call npm run build
if %errorlevel% neq 0 (
    echo ERROR: Asset build failed!
    pause
    exit /b 1
)
echo.

echo [2/4] Clearing Laravel caches...
php artisan optimize:clear
if %errorlevel% neq 0 (
    echo ERROR: Cache clear failed!
    pause
    exit /b 1
)
echo.

echo [3/4] Generating Ziggy routes...
php artisan ziggy:generate
if %errorlevel% neq 0 (
    echo WARNING: Ziggy generation failed (may not be critical)
)
echo.

echo [4/4] All done!
echo.
echo ========================================
echo Dashboard refreshed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Restart your Laravel server
echo 2. Hard refresh browser (Ctrl+Shift+R)
echo 3. Try navigating again
echo.
pause
