@echo off
title Instalador de Dependencias de MusicDown
color 0B
cd /d "%~dp0"
echo ===================================================
echo   INSTALANDO YT-DLP Y FFMPEG...
echo   (Esto abrira la conexion a Github para descargar)
echo ===================================================
echo.

set PS_EXE=C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe

:: 1. yt-dlp
echo [1/2] Descargando yt-dlp.exe (aprox 18MB)...
"%PS_EXE%" -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $web = New-Object System.Net.WebClient; $web.Headers.Add('User-Agent', 'Mozilla/5.0'); $web.DownloadFile('https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp.exe', 'yt-dlp.exe')"

if exist "yt-dlp.exe" echo [OK] yt-dlp descargado correctamente.
if not exist "yt-dlp.exe" echo [ERROR] No se pudo descargar yt-dlp. Revisa tu conexion.
echo.

:: 2. FFmpeg
if exist "ffmpeg.exe" goto skip_ffmpeg

echo [2/2] Descargando FFmpeg (aprox 130MB, puede tardar)...
"%PS_EXE%" -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $web = New-Object System.Net.WebClient; $web.Headers.Add('User-Agent', 'Mozilla/5.0'); $web.DownloadFile('https://github.com/yt-dlp/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip', 'ffmpeg.zip')"
    
if not exist "ffmpeg.zip" goto error_ffmpeg

echo [OK] FFmpeg descargado. Extrayendo archivos...
"%PS_EXE%" -NoProfile -ExecutionPolicy Bypass -Command "Expand-Archive -Path 'ffmpeg.zip' -DestinationPath 'ffmpeg_temp' -Force; Get-ChildItem -Path 'ffmpeg_temp' -Recurse -Filter 'ffmpeg.exe' | Copy-Item -Destination '.' -Force; Get-ChildItem -Path 'ffmpeg_temp' -Recurse -Filter 'ffprobe.exe' | Copy-Item -Destination '.' -Force; Remove-Item -Path 'ffmpeg_temp' -Recurse -Force; Remove-Item -Path 'ffmpeg.zip' -Force"
echo [OK] Extraccion finalizada.
goto finish

:skip_ffmpeg
echo [2/2] FFmpeg ya esta instalado. Omitiendo...
goto finish

:error_ffmpeg
echo [ERROR] No se pudo descargar FFmpeg.

:finish
echo.
echo ===================================================
echo   INSTALACION FINALIZADA
echo   Puedes cerrar esta ventana y volver al programa.
echo ===================================================
pause
