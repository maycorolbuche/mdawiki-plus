@echo off
setlocal
cd /d "%~dp0"
node scripts/pages/dev.mjs
exit /b %errorlevel%
