::portal.bat
@echo off

Set SCRIPT_PATH=%~dp0

php -d error_reporting=30711 %SCRIPT_PATH%\portal.php %*