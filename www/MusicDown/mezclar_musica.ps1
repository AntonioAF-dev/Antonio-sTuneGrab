# Script de Mezcla Aleatoria de Música

param (
    [string]$TargetDir = ""
)

$ErrorActionPreference = "Stop"
$musicPath = if (-not [string]::IsNullOrWhiteSpace($TargetDir)) { $TargetDir } else { $PSScriptRoot }
if ([string]::IsNullOrEmpty($musicPath)) { $musicPath = "I:\" }

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  MEZCLADOR ALEATORIO DE MÚSICA           " -ForegroundColor Yellow
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Carpeta: $musicPath"

$musicExtensions = @('.mp3', '.webm', '.m4a', '.flac', '.wav')

$allFiles = Get-ChildItem -LiteralPath $musicPath -File | Where-Object { 
    $musicExtensions -contains $_.Extension.ToLower() -and -not $_.Name.StartsWith("_temp_")
}

if ($allFiles.Count -eq 0) {
    Write-Host "No se encontraron archivos de música para mezclar." -ForegroundColor Red
    return
}

Write-Host "Encontradas $($allFiles.Count) canciones en la carpeta." -ForegroundColor Green
Write-Host "Procesando nombres..."

$items = @()
foreach ($file in $allFiles) {
    # Eliminar correlativos anteriores al inicio del nombre (ej: "001 - ", "043.", "09 ")
    $cleanName = $file.Name -replace '^\d{1,4}[\s\._-]+', ''
    if ([string]::IsNullOrWhiteSpace($cleanName)) {
        $cleanName = $file.Name
    }
    
    # Archivo .lrc de letras si existe
    $lrcFile = $null
    $lrcPath = [System.IO.Path]::ChangeExtension($file.FullName, ".lrc")
    if (Test-Path -LiteralPath $lrcPath) {
        $lrcFile = Get-Item -LiteralPath $lrcPath
    }

    $items += [PSCustomObject]@{
        OriginalFile = $file
        CleanName    = $cleanName
        LrcFile      = $lrcFile
    }
}

Write-Host "Mezclando aleatoriamente..." -ForegroundColor Cyan
$shuffled = $items | Get-Random -Count $items.Count

Write-Host "Fase 1/2: Asignando nombres temporales de seguridad..." -ForegroundColor Gray
$tempList = @()
$counter = 1

foreach ($item in $shuffled) {
    $guid = [System.Guid]::NewGuid().ToString("N").Substring(0, 8)
    $tempMusicName = "_temp_${guid}_$($item.CleanName)"
    $tempMusicPath = Join-Path -Path $musicPath -ChildPath $tempMusicName
    
    Rename-Item -LiteralPath $item.OriginalFile.FullName -NewName $tempMusicName
    
    $tempLrcPath = $null
    if ($null -ne $item.LrcFile) {
        $tempLrcName = "_temp_${guid}_$([System.IO.Path]::GetFileNameWithoutExtension($item.CleanName)).lrc"
        Rename-Item -LiteralPath $item.LrcFile.FullName -NewName $tempLrcName
        $tempLrcPath = Join-Path -Path $musicPath -ChildPath $tempLrcName
    }

    $tempList += [PSCustomObject]@{
        TempMusicPath = $tempMusicPath
        TempLrcPath   = $tempLrcPath
        CleanName     = $item.CleanName
        Index         = $counter
    }
    $counter++
}

Write-Host "Fase 2/2: Asignando orden aleatorio (001 - ...)..." -ForegroundColor Gray
$total = $tempList.Count
$digits = if ($total -ge 1000) { 4 } else { 3 }

foreach ($tempItem in $tempList) {
    $numStr = $tempItem.Index.ToString().PadLeft($digits, '0')
    $finalMusicName = "$numStr - $($tempItem.CleanName)"
    
    Rename-Item -LiteralPath $tempItem.TempMusicPath -NewName $finalMusicName
    
    if ($null -ne $tempItem.TempLrcPath) {
        $cleanBase = [System.IO.Path]::GetFileNameWithoutExtension($tempItem.CleanName)
        $finalLrcName = "$numStr - $cleanBase.lrc"
        Rename-Item -LiteralPath $tempItem.TempLrcPath -NewName $finalLrcName
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "  ¡MEZCLA COMPLETADA CON ÉXITO!           " -ForegroundColor Green
Write-Host "  Se mezclaron $total canciones.          " -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
