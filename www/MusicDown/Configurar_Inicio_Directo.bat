@echo off
title Configurar Inicio Directo a MusicDown
color 0A
echo ====================================================
echo    CONFIGURANDO PHPDESKTOP PARA ABRIR MUSICDOWN
echo ====================================================
echo.

cd /d "%~dp0"

:: 1. Crear / Sobrescribir www/index.php para redireccion
echo [1/2] Configurando redireccion en www/index.php...
(
  echo ^<?php
  echo header^("Location: MusicDown/index.php"^);
  echo exit;
  echo ?^>
) > "..\index.php"

:: 2. Modificar settings.json si existe
echo [2/2] Actualizando start_page en settings.json...
if exist "..\..\settings.json" (
  "%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -Command "$json = Get-Content '..\..\settings.json' -Raw | ConvertFrom-Json; $json.request_handler.start_page = 'MusicDown/index.php'; $json | ConvertTo-Json -Depth 10 | Set-Content '..\..\settings.json'"
)

echo.
echo ====================================================
echo    ¡CONFIGURACION COMPLETADA CON EXITO!
echo ====================================================
echo Ahora al abrir phpdesktop-chrome.exe cargara directamente
echo tu aplicacion MusicDown Pro.
echo.
pause
