# Script de Descarga de Playlist de YouTube a MP3
$ErrorActionPreference = "Continue"
$workDir = $PSScriptRoot
if ([string]::IsNullOrEmpty($workDir)) { $workDir = "I:\" }

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "    DESCARGADOR DE PLAYLISTS DE YOUTUBE   " -ForegroundColor Yellow
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

$ytDlpPath = Join-Path $workDir "yt-dlp.exe"
$ffmpegPath = Join-Path $workDir "ffmpeg.exe"

# Eliminar ejecutable si esta incompleto (<10MB)
if (Test-Path $ytDlpPath) {
    $fileItem = Get-Item $ytDlpPath
    if ($fileItem.Length -lt 10000000) {
        Write-Host "Eliminando ejecutable corrupto/incompleto..." -ForegroundColor Yellow
        Remove-Item $ytDlpPath -Force
    }
}

# 1. Descargar yt-dlp.exe completo
if (-not (Test-Path $ytDlpPath)) {
    Write-Host "Descargando yt-dlp.exe (18 MB)..." -ForegroundColor Yellow
    $url = "https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp.exe"
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        $web = New-Object System.Net.WebClient
        $web.Headers.Add("User-Agent", "Mozilla/5.0")
        $web.DownloadFile($url, $ytDlpPath)
        Write-Host "yt-dlp.exe descargado con éxito." -ForegroundColor Green
    } catch {
        Write-Host "Error al descargar automáticamente: $_" -ForegroundColor Red
        pause
        return
    }
}

# 2. Descargar FFmpeg para conversión a MP3 si no existe
if (-not (Test-Path $ffmpegPath)) {
    Write-Host "Descargando FFmpeg para convertir archivos a MP3..." -ForegroundColor Yellow
    $zipPath = Join-Path $workDir "ffmpeg.zip"
    $tempDir = Join-Path $workDir "ffmpeg_temp"
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        $web = New-Object System.Net.WebClient
        $web.Headers.Add("User-Agent", "Mozilla/5.0")
        $web.DownloadFile("https://github.com/yt-dlp/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip", $zipPath)
        Expand-Archive -Path $zipPath -DestinationPath $tempDir -Force
        Get-ChildItem -Path $tempDir -Recurse -Filter "ffmpeg.exe" | Copy-Item -Destination $workDir -Force
        Get-ChildItem -Path $tempDir -Recurse -Filter "ffprobe.exe" | Copy-Item -Destination $workDir -Force
        Remove-Item -Path $tempDir -Recurse -Force
        Remove-Item -Path $zipPath -Force
        Write-Host "FFmpeg instalado correctamente." -ForegroundColor Green
    } catch {
        Write-Host "No se pudo descargar FFmpeg. Las canciones se guardarán en formato .webm/.m4a de alta calidad." -ForegroundColor Yellow
    }
}

$playlistUrl = "https://www.youtube.com/watch?v=nQRnqMDZC4g&list=RDnQRnqMDZC4g&start_radio=1"

Write-Host ""
Write-Host "Iniciando descarga de la playlist..." -ForegroundColor Cyan
Write-Host "URL: $playlistUrl" -ForegroundColor Gray
Write-Host ""

$outputFormat = Join-Path $workDir "%(title)s.%(ext)s"

& $ytDlpPath --ffmpeg-location $workDir -x --audio-format mp3 --audio-quality 0 --yes-playlist --no-mtime -o "$outputFormat" "$playlistUrl"

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "     ¡DESCARGA FINALIZADA!                " -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

$mixAnswer = Read-Host "¿Deseas mezclar las canciones ahora con 'mezclar_musica.ps1'? (S/N)"
if ($mixAnswer -match "^[sS]") {
    $mixerScript = Join-Path $workDir "mezclar_musica.ps1"
    if (Test-Path $mixerScript) {
        & $mixerScript
    }
}
