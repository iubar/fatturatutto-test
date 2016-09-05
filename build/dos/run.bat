@echo off
REM Questo script evita che la finestra di phpunit venga chiusa al termine dell'esecuzione
cmd /k "%~dp0phptest.bat"
pause