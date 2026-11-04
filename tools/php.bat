@echo off
REM Wrapper around the system PHP that applies the project-local php.ini
REM (tools\php-local.ini), which enables pdo_pgsql, pgsql, pdo_sqlite,
REM sqlite3, zip, fileinfo, intl and gd without modifying the system install.
REM
REM Usage: tools\php.bat [php args...]
"C:\Program Files\php-8.5.12-1-Win32-vs17-x64\php.exe" -c "%~dp0php-local.ini" %*
