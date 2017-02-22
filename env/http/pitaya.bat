::pitaya.bat
@echo off

cd /d %~dp0
Set WORKING_DIR=%CD%
php .\gateway.php %*
