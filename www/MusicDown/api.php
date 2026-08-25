<?php
/**
 * Backend API para MusicDown Pro - PHPDesktop
 * Maneja descargas yt-dlp, instalación de dependencias, mezcla y lista de archivos.
 */

// Evitar límites de tiempo de ejecución de PHP para descargas largas
set_time_limit(0);
ini_set('implicit_flush', 1);

$workDir = __DIR__;
chdir($workDir);

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';
$saveDir = $_GET['save_dir'] ?? $_POST['save_dir'] ?? '';

if (empty($saveDir)) {
    $saveDir = $workDir;
} else {
    // Asegurar que el directorio de guardado existe
    if (!is_dir($saveDir)) {
        @mkdir($saveDir, 0777, true);
    }
}

if ($action === 'status') {
    header('Content-Type: application/json');
    $ytDlpPath = $workDir . DIRECTORY_SEPARATOR . 'yt-dlp.exe';
    $ffmpegPath = $workDir . DIRECTORY_SEPARATOR . 'ffmpeg.exe';

    $ytOk = false;
    if (file_exists($ytDlpPath)) {
        if (filesize($ytDlpPath) >= 10000000) {
            $ytOk = true;
        }
    }

    $ffmpegOk = file_exists($ffmpegPath);

    echo json_encode([
        'ytdlp' => $ytOk,
        'ffmpeg' => $ffmpegOk,
        'workdir' => $workDir
    ]);
    exit;
}

if ($action === 'pick_folder') {
    header('Content-Type: application/json');
    $psPath = $workDir . DIRECTORY_SEPARATOR . 'pick_folder.ps1';
    $cmd = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe -Sta -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "' . $psPath . '"';
    $output = shell_exec($cmd);
    $path = $output ? trim((string)$output) : '';
    echo json_encode(['path' => $path]);
    exit;
}

if ($action === 'open_folder') {
    header('Content-Type: application/json');
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        pclose(popen('C:\\Windows\\explorer.exe "' . $saveDir . '"', 'r'));
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'open_url') {
    header('Content-Type: application/json');
    $url = $_GET['url'] ?? '';
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start "" "' . $url . '"', 'r'));
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'list_files') {
    header('Content-Type: application/json');
    $extensions = ['mp3', 'webm', 'm4a', 'flac', 'wav', 'mp4'];
    $files = [];

    if (is_dir($saveDir)) {
        $dir = new DirectoryIterator($saveDir);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isFile()) {
                $ext = strtolower($fileinfo->getExtension());
                $fileName = $fileinfo->getFilename();
                if (in_array($ext, $extensions) && strpos($fileName, '_temp_') !== 0) {
                    $bytes = $fileinfo->getSize();
                    $sizeMB = round($bytes / (1024 * 1024), 2);
                    $files[] = [
                        'name' => $fileName,
                        'size' => $sizeMB . ' MB',
                        'mtime' => date('H:i:s', $fileinfo->getMTime()),
                        'ext' => $ext
                    ];
                }
            }
        }
    }

    // Ordenar por fecha de modificación más reciente
    usort($files, function($a, $b) {
        return strcmp($b['mtime'], $a['mtime']);
    });

    echo json_encode(['files' => $files]);
    exit;
}

// Función helper para SSE (Server-Sent Events)
function sendEvent($type, $message, $extra = []) {
    $payload = array_merge(['type' => $type, 'message' => $message], $extra);
    echo "data: " . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (ob_get_level() > 0) ob_flush();
    flush();
}

if ($action === 'install_deps') {
    header('Content-Type: application/json');
    $batPath = $workDir . DIRECTORY_SEPARATOR . 'Instalar_Dependencias.bat';
    pclose(popen('start "" "' . $batPath . '"', 'r'));
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'mix') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    sendEvent('info', 'Ejecutando script de mezcla aleatoria mezclar_musica.ps1...');

    $cmd = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe -NoProfile -ExecutionPolicy Bypass -File "' . $workDir . DIRECTORY_SEPARATOR . 'mezclar_musica.ps1" -TargetDir "' . $saveDir . '"';
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $process = proc_open($cmd, $descriptors, $pipes, $workDir);

    if (is_resource($process)) {
        while (!feof($pipes[1])) {
            $line = fgets($pipes[1]);
            if ($line !== false) {
                $line = trim($line);
                if (!empty($line)) {
                    $type = 'info';
                    if (strpos($line, 'COMPLETADA') !== false || strpos($line, 'Encontradas') !== false) {
                        $type = 'success';
                    } elseif (strpos($line, 'Error') !== false || strpos($line, 'No se encontraron') !== false) {
                        $type = 'error';
                    }
                    sendEvent($type, $line);
                }
            }
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }

    sendEvent('complete', 'Proceso de mezcla terminado.');
    exit;
}

if ($action === 'download') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    $url = trim($_GET['url'] ?? '');
    $limit = $_GET['limit'] ?? '10';
    $audioFormat = $_GET['format'] ?? 'mp3';
    $mixAfter = isset($_GET['mix']) ? (int)$_GET['mix'] : 0;
    $singleSong = isset($_GET['single']) ? (int)$_GET['single'] : 0;

    if (empty($url)) {
        sendEvent('error', 'Error: Debes proporcionar una URL válida de YouTube o Playlist.');
        sendEvent('complete', 'Descarga cancelada por falta de URL.');
        exit;
    }

    sendEvent('info', '==================================================');
    sendEvent('info', 'INICIANDO PROCESO DE DESCARGA');
    sendEvent('info', 'URL: ' . $url);
    if ($singleSong === 0 && $limit !== 'all') {
        sendEvent('info', 'Límite configurado: Máximo ' . $limit . ' canciones');
    } elseif ($singleSong === 1) {
        sendEvent('info', 'Modo: Descarga individual');
    } else {
        sendEvent('info', 'Límite configurado: Descargar todas las canciones disponibles');
    }
    if ($audioFormat === 'mp4') {
        sendEvent('info', 'Formato de descarga: MP4 (Video)');
    } else {
        sendEvent('info', 'Formato de audio: ' . strtoupper($audioFormat));
    }
    sendEvent('info', '==================================================');

    $ytDlpPath = $workDir . DIRECTORY_SEPARATOR . 'yt-dlp.exe';
    if (!file_exists($ytDlpPath) || filesize($ytDlpPath) < 10000000) {
        sendEvent('error', 'Falta yt-dlp.exe (o esta corrupto). Haz clic en "Verificar / Instalar Motores" en la interfaz y espera a que termine de descargar en la consola negra.');
        sendEvent('complete', 'Descarga cancelada por falta de dependencias.');
        exit;
    }

    // Construcción del comando yt-dlp
    $cmdParts = [];
    $cmdParts[] = '"' . $ytDlpPath . '"';
    $cmdParts[] = '--ffmpeg-location "."';
    
    if ($audioFormat === 'mp4') {
        $cmdParts[] = '-f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best"';
        $cmdParts[] = '--merge-output-format mp4';
    } else {
        $cmdParts[] = '-x';
        $cmdParts[] = '--audio-format ' . escapeshellarg($audioFormat);
        $cmdParts[] = '--audio-quality 0';
    }
    
    $cmdParts[] = '--no-mtime';

    if ($singleSong === 1) {
        $cmdParts[] = '--no-playlist';
    } else {
        $cmdParts[] = '--yes-playlist';
        if ($limit !== 'all') {
            $cmdParts[] = '--max-downloads ' . escapeshellarg($limit);
        }
    }

    $cmdParts[] = '-o "' . $saveDir . DIRECTORY_SEPARATOR . '%(title)s.%(ext)s"';
    $cmdParts[] = escapeshellarg($url);

    $command = implode(' ', $cmdParts) . ' 2>&1';

    sendEvent('dim', 'Comando: ' . $command);

    // Para evitar deadlocks, capturamos todo en stdout gracias al 2>&1
    $descriptors = [
        1 => ['pipe', 'w']
    ];

    $process = proc_open($command, $descriptors, $pipes, $workDir);

    if (is_resource($process)) {
        $currentSong = 0;
        $totalSongs = $maxDownloads;

        while (!feof($pipes[1])) {
            $line = fgets($pipes[1]);
            if ($line !== false) {
                $line = trim($line);
                if (empty($line)) continue;

                $type = 'info';

                // Análisis de líneas de yt-dlp para progreso y estados
                if (preg_match('/\[download\]\s+Downloading item\s+(\d+)\s+of\s+(\d+)/i', $line, $matches)) {
                    $currentSong = intval($matches[1]);
                    $totalSongs = intval($matches[2]);
                    $type = 'warning';
                    sendEvent('progress', $line, ['current' => $currentSong, 'total' => $totalSongs]);
                    continue;
                }

                if (preg_match('/\[download\]\s+([\d\.]+)\%/i', $line, $matches)) {
                    $pct = floatval($matches[1]);
                    $type = 'info';
                    sendEvent('progress_pct', $line, ['pct' => $pct, 'current' => $currentSong, 'total' => $totalSongs]);
                    continue;
                }

                if (strpos($line, '[download] Destination:') !== false || strpos($line, 'has already been downloaded') !== false) {
                    $type = 'success';
                } elseif (strpos($line, 'ERROR:') !== false || strpos($line, 'WARNING:') !== false) {
                    $type = (strpos($line, 'ERROR:') !== false) ? 'error' : 'warning';
                } elseif (strpos($line, '[ExtractAudio]') !== false || strpos($line, '[ffmpeg]') !== false) {
                    $type = 'success';
                }

                sendEvent($type, $line);
            }
        }

        fclose($pipes[1]);
        $exitCode = proc_close($process);

        if ($exitCode === 0 || $exitCode === 101) { // 101 es el exit code de yt-dlp cuando alcanza --max-downloads
            sendEvent('success', '¡DESCARGA COMPLETADA CON ÉXITO!');

            if ($mixAfter === 1) {
                sendEvent('warning', 'Iniciando mezcla aleatoria automática de canciones...');
                $cmdMix = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe -NoProfile -ExecutionPolicy Bypass -File "' . $workDir . DIRECTORY_SEPARATOR . 'mezclar_musica.ps1" -TargetDir "' . $saveDir . '" 2>&1';
                $descriptorsMix = [ 1 => ['pipe', 'w'] ];
                $procMix = proc_open($cmdMix, $descriptorsMix, $pipesMix, $workDir);
                if (is_resource($procMix)) {
                    while (!feof($pipesMix[1])) {
                        $mLine = fgets($pipesMix[1]);
                        if ($mLine !== false) {
                            $mLine = trim($mLine);
                            if (!empty($mLine)) sendEvent('info', '[Mezclador] ' . $mLine);
                        }
                    }
                    fclose($pipesMix[1]);
                    proc_close($procMix);
                }
                sendEvent('success', '¡Mezcla aleatoria completada!');
            }
        } else {
            sendEvent('error', 'El proceso de descarga finalizó con código: ' . $exitCode);
        }
    } else {
        sendEvent('error', 'No se pudo iniciar el proceso de yt-dlp.exe');
    }

    sendEvent('complete', 'Proceso de descarga terminado.');
    exit;
}
