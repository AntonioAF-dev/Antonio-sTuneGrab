@echo off
title Mezclador Aleatorio de Musica
echo Ejecutando mezcla aleatoria de canciones...
if exist "%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" (
    "%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -File "%~dp0mezclar_musica.ps1"
) else (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0mezclar_musica.ps1"
)
echo.
pause
