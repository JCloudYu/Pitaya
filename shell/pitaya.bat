@echo off

SET PITAYA_ROOT=%~dp0:~0,-1%
php %PITAYA_ROOT%\pitaya.php %*
