@echo off
REM Define database credentials
set USER=root
set PASSWORD=
set DB_NAME=attendance_system
set TABLE_NAME=attendance

REM Run PHP export script
php C:\xampp\htdocs\attendance_dis\export.php
pause
exit
