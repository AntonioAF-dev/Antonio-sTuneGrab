@echo off
title Descargador de Playlist de YouTube
echo ====================================================
echo      DESCARGADOR AUTOMATICO DE PLAYLISTS MP3
echo ====================================================
echo.

cd /d "%~dp0"

:: 1. Comprobar o eliminar yt-dlp.exe si esta corrupto
if exist "yt-dlp.exe" (
    for %%F in ("yt-dlp.exe") do (
        if %%~zF LSS 10000000 (
            echo Eliminando archivo yt-dlp.exe incompleto...
            del /f /q "yt-dlp.exe"
        )
    )
)

if exist "yt-dlp.exe" goto CHECK_FFMPEG

echo [1/3] Descargando motor de descarga (yt-dlp.exe)...
echo Por favor espera unos segundos...
echo.

"%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $web = New-Object System.Net.WebClient; $web.Headers.Add('User-Agent', 'Mozilla/5.0'); $web.DownloadFile('https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp.exe', 'yt-dlp.exe')"

:CHECK_FFMPEG
if exist "ffmpeg.exe" goto START_DOWNLOAD

echo [1.5/3] Descargando convertidor FFmpeg para conversion a MP3...
echo Por favor espera unos segundos mientras se instala FFmpeg...
echo.

"%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $zip = 'ffmpeg.zip'; $web = New-Object System.Net.WebClient; $web.Headers.Add('User-Agent', 'Mozilla/5.0'); $web.DownloadFile('https://github.com/yt-dlp/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip', $zip); Expand-Archive -Path $zip -DestinationPath 'ffmpeg_temp' -Force; Get-ChildItem -Path 'ffmpeg_temp' -Recurse -Filter 'ffmpeg.exe' | Copy-Item -Destination '.' -Force; Get-ChildItem -Path 'ffmpeg_temp' -Recurse -Filter 'ffprobe.exe' | Copy-Item -Destination '.' -Force; Remove-Item -Path 'ffmpeg_temp' -Recurse -Force; Remove-Item -Path $zip -Force"

:START_DOWNLOAD
echo [2/3] Descargando y convirtiendo playlist a MP3...
echo.

"yt-dlp.exe" --ffmpeg-location "." -x --audio-format mp3 --audio-quality 0 --yes-playlist --no-mtime -o "%%(title)s.%%(ext)s" "https://www.youtube.com/watch?v=nQRnqMDZC4g&list=RDnQRnqMDZC4g&start_radio=1"

echo.
echo ====================================================
echo      ¡DESCARGA Y CONVERSION COMPLETADAS!
echo ====================================================
echo.

set /p mezclar="Deseas mezclar las canciones ahora? (S/N): "
if /i "%mezclar%"=="S" goto RUN_MIXER
goto END

:RUN_MIXER
echo [3/3] Mezclando canciones...
if exist "%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" (
    "%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -File "mezclar_musica.ps1"
) else (
    powershell -NoProfile -ExecutionPolicy Bypass -File "mezclar_musica.ps1"
)

:END
echo.
echo Proceso finalizado.
pause
