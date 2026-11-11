@echo off
setlocal
cd /d "%~dp0"
node scripts/pages/build.mjs --local
if errorlevel 1 exit /b %errorlevel%
call npx --yes wrangler@4 pages dev dist --ip 0.0.0.0 --port 8006 --binding LOCAL_CATALOG=true
exit /b %errorlevel%
