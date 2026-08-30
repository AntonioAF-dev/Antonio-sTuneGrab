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

    // Ordenar naturalmente por nombre (001 -, 002 -, 010 - en secuencia perfecta)
    usort($files, function($a, $b) {
        return strnatcasecmp($a['name'], $b['name']);
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

    $safeTargetDir = $saveDir;
    if (substr($safeTargetDir, -1) === '\\' || substr($safeTargetDir, -1) === '/') {
        $safeTargetDir .= '\\';
    }
    $cmd = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe -NoProfile -ExecutionPolicy Bypass -File "' . $workDir . DIRECTORY_SEPARATOR . 'mezclar_musica.ps1" -TargetDir "' . $safeTargetDir . '"';
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

if ($action === 'get_clipboard') {
    header('Content-Type: application/json');
    $clip = shell_exec('powershell Get-Clipboard 2> NUL');
    echo json_encode(['text' => trim($clip ?? '')]);
    exit;
}

if ($action === 'open_file_location') {
    header('Content-Type: application/json');
    $fileName = trim($_GET['file'] ?? $_POST['file'] ?? '');
    if (empty($fileName)) {
        echo json_encode(['success' => false, 'error' => 'Nombre de archivo vacío']);
        exit;
    }
    $filePath = $saveDir . DIRECTORY_SEPARATOR . $fileName;
    if (file_exists($filePath)) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('C:\\Windows\\explorer.exe /select,"' . $filePath . '"', 'r'));
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Archivo no encontrado']);
    }
    exit;
}

if ($action === 'rename_file') {
    header('Content-Type: application/json');
    $oldName = trim($_POST['old_name'] ?? $_GET['old_name'] ?? '');
    $newName = trim($_POST['new_name'] ?? $_GET['new_name'] ?? '');
    if (empty($oldName) || empty($newName)) {
        echo json_encode(['success' => false, 'error' => 'Nombre de archivo inválido']);
        exit;
    }
    // Sanitizar nuevo nombre manteniendo extensión si no la puso
    $cleanNewName = preg_replace('/[\\/\\\\:\*\?"<>\|]/', '', $newName);
    $oldExt = pathinfo($oldName, PATHINFO_EXTENSION);
    $newExt = pathinfo($cleanNewName, PATHINFO_EXTENSION);
    if (empty($newExt) && !empty($oldExt)) {
        $cleanNewName .= '.' . $oldExt;
    }
    
    $oldPath = $saveDir . DIRECTORY_SEPARATOR . $oldName;
    $newPath = $saveDir . DIRECTORY_SEPARATOR . $cleanNewName;
    
    if (!file_exists($oldPath)) {
        echo json_encode(['success' => false, 'error' => 'El archivo original no existe']);
        exit;
    }
    if ($oldPath !== $newPath && file_exists($newPath)) {
        echo json_encode(['success' => false, 'error' => 'Ya existe un archivo con ese nombre']);
        exit;
    }
    
    if (@rename($oldPath, $newPath)) {
        echo json_encode(['success' => true, 'new_name' => $cleanNewName]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo renombrar (posible archivo en uso)']);
    }
    exit;
}

if ($action === 'delete_file') {
    header('Content-Type: application/json');
    $fileName = trim($_GET['file'] ?? '');
    if (empty($fileName)) {
        echo json_encode(['success' => false, 'error' => 'Nombre de archivo vacío']);
        exit;
    }
    $filePath = $saveDir . DIRECTORY_SEPARATOR . $fileName;
    if (file_exists($filePath) && is_file($filePath)) {
        if (@unlink($filePath)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo borrar el archivo (puede estar en uso)']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Archivo no encontrado']);
    }
    exit;
}

if ($action === 'video_audio') {
    $fileName = trim($_GET['file'] ?? '');
    $filePath = $saveDir . DIRECTORY_SEPARATOR . $fileName;

    if (empty($fileName) || !file_exists($filePath) || !is_file($filePath)) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'antonio_video_audio';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    $cacheKey = md5($filePath . '_' . filemtime($filePath));
    $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $cacheKey . '.mp3';

    // Si no existe el caché de audio MP3, extraerlo con ffmpeg.exe
    if (!file_exists($cacheFile) || filesize($cacheFile) === 0) {
        $ffmpegPath = $workDir . DIRECTORY_SEPARATOR . 'ffmpeg.exe';
        if (file_exists($ffmpegPath)) {
            $cmd = '"' . $ffmpegPath . '" -y -i "' . $filePath . '" -vn -c:a libmp3lame -b:a 192k "' . $cacheFile . '" 2>&1';
            shell_exec($cmd);
        }
    }

    $targetPath = (file_exists($cacheFile) && filesize($cacheFile) > 0) ? $cacheFile : $filePath;

    $size = filesize($targetPath);
    $length = $size;
    $start = 0;
    $end = $size - 1;

    header('Content-Type: audio/mpeg');
    header('Accept-Ranges: bytes');

    if (isset($_SERVER['HTTP_RANGE'])) {
        $c_start = $start;
        $c_end = $end;
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        if ($range == '-') {
            $c_start = $size - substr($range, 1);
        } else {
            $range = explode('-', $range);
            $c_start = $range[0];
            $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size - 1;
        }
        $c_end = ($c_end > $end) ? $end : $c_end;
        if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        $start = $c_start;
        $end = $c_end;
        $length = $end - $start + 1;
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$size");
    }

    header("Content-Length: " . $length);

    $fp = fopen($targetPath, 'rb');
    if ($fp) {
        fseek($fp, $start);
        $buffer = 1024 * 64;
        while (!feof($fp) && ($pos = ftell($fp)) <= $end) {
            if ($pos + $buffer > $end) {
                $buffer = $end - $pos + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
    }
    exit;
}

if ($action === 'play') {
    $fileName = trim($_GET['file'] ?? '');
    $filePath = $saveDir . DIRECTORY_SEPARATOR . $fileName;
    
    if (empty($fileName) || !file_exists($filePath) || !is_file($filePath)) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mime = 'audio/mpeg';
    if ($ext === 'webm') $mime = 'audio/webm';
    if ($ext === 'mp4') $mime = 'video/mp4';
    if ($ext === 'm4a') $mime = 'audio/mp4';
    if ($ext === 'wav') $mime = 'audio/wav';
    if ($ext === 'flac') $mime = 'audio/flac';

    $size = filesize($filePath);
    $length = $size;
    $start = 0;
    $end = $size - 1;

    header('Content-Type: ' . $mime);
    header('Accept-Ranges: bytes');

    if (isset($_SERVER['HTTP_RANGE'])) {
        $c_start = $start;
        $c_end = $end;
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        if ($range == '-') {
            $c_start = $size - substr($range, 1);
        } else {
            $range = explode('-', $range);
            $c_start = $range[0];
            $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size - 1;
        }
        $c_end = ($c_end > $end) ? $end : $c_end;
        if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        $start = $c_start;
        $end = $c_end;
        $length = $end - $start + 1;
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$size");
    }

    header("Content-Length: " . $length);

    $fp = fopen($filePath, 'rb');
    if ($fp) {
        fseek($fp, $start);
        $buffer = 1024 * 64;
        while (!feof($fp) && ($pos = ftell($fp)) <= $end) {
            if ($pos + $buffer > $end) {
                $buffer = $end - $pos + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
    }
    exit;
}

if ($action === 'cancel') {
    header('Content-Type: application/json');
    shell_exec('taskkill /F /IM yt-dlp.exe /T 2>&1');
    shell_exec('taskkill /F /IM ffmpeg.exe /T 2>&1');
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'search') {
    header('Content-Type: application/json');
    $query = trim($_GET['q'] ?? '');
    $limit = (int)($_GET['limit'] ?? 10);
    if (empty($query)) {
        echo json_encode(['success' => false, 'error' => 'Búsqueda vacía']);
        exit;
    }
    
    $ytDlpPath = $workDir . DIRECTORY_SEPARATOR . 'yt-dlp.exe';
    if (!file_exists($ytDlpPath)) {
        echo json_encode(['success' => false, 'error' => 'Falta yt-dlp']);
        exit;
    }

    $cmd = '"' . $ytDlpPath . '" "ytsearch' . $limit . ':' . $query . '" --dump-json --flat-playlist 2>&1';
    $output = shell_exec($cmd);
    
    $results = [];
    if ($output) {
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data && isset($data['id'])) {
                $thumbnail = '';
                if (isset($data['thumbnails']) && is_array($data['thumbnails']) && count($data['thumbnails']) > 0) {
                    $thumbnail = $data['thumbnails'][0]['url'];
                } elseif (isset($data['thumbnail'])) {
                    $thumbnail = $data['thumbnail'];
                }

                $url = $data['url'] ?? ($data['webpage_url'] ?? ('https://www.youtube.com/watch?v=' . $data['id']));
                
                // Excluir canales o listas (solo permitir videos)
                if (strpos($url, 'watch?v=') === false && strpos($url, 'youtu.be') === false) {
                    continue;
                }

                $results[] = [
                    'id' => $data['id'],
                    'title' => $data['title'] ?? 'Desconocido',
                    'duration' => $data['duration_string'] ?? '?',
                    'thumbnail' => $thumbnail,
                    'uploader' => $data['uploader'] ?? ($data['channel'] ?? ''),
                    'url' => $url
                ];
            }
        }
    }
    
    echo json_encode(['success' => true, 'results' => $results]);
    exit;
}

if ($action === 'clean') {
    header('Content-Type: application/json');
    if (empty($saveDir) || !is_dir($saveDir)) {
        echo json_encode(['success' => false, 'error' => 'Directorio inválido']);
        exit;
    }

    $deletedCount = 0;
    $freedBytes = 0;
    
    $files = glob($saveDir . DIRECTORY_SEPARATOR . '*');
    $musicFiles = [];

    foreach ($files as $file) {
        if (!is_file($file)) continue;
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $basename = basename($file);
        $size = filesize($file);

        // 1. Eliminar archivos temporales o incompletos
        if (in_array($ext, ['part', 'ytdl', 'temp']) || strpos($basename, '.f') !== false && strpos($basename, '.webm') !== false) {
            $freedBytes += $size;
            @unlink($file);
            $deletedCount++;
            continue;
        }

        // 2. Agrupar para detectar duplicados (ignorar "001 - ")
        if (in_array($ext, ['mp3', 'webm', 'm4a', 'flac', 'wav', 'mp4'])) {
            $cleanName = preg_replace('/^\d{1,4}[\s\._-]+/', '', $basename);
            if (!isset($musicFiles[$cleanName])) {
                $musicFiles[$cleanName] = [];
            }
            $musicFiles[$cleanName][] = ['path' => $file, 'size' => $size, 'mtime' => filemtime($file)];
        }
    }

    // Procesar duplicados
    foreach ($musicFiles as $cleanName => $items) {
        if (count($items) > 1) {
            // Ordenar por fecha, más recientes primero
            usort($items, function($a, $b) {
                return $b['mtime'] <=> $a['mtime'];
            });
            // Mantener el más reciente (índice 0), eliminar los demás
            for ($i = 1; $i < count($items); $i++) {
                $freedBytes += $items[$i]['size'];
                @unlink($items[$i]['path']);
                $deletedCount++;
            }
        }
    }

    $freedMB = round($freedBytes / (1024 * 1024), 2);
    echo json_encode(['success' => true, 'deleted_count' => $deletedCount, 'freed_mb' => $freedMB]);
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
    $metadata = isset($_GET['metadata']) ? (int)$_GET['metadata'] : 0;

    if (empty($url)) {
        sendEvent('error', 'Error: Debes proporcionar un enlace válido.');
        sendEvent('complete', 'Descarga cancelada por falta de datos.');
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
        $cmdParts[] = '-f "bestvideo[vcodec^=avc1][ext=mp4]+bestaudio[acodec^=mp4a]/bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best"';
        $cmdParts[] = '--merge-output-format mp4';
        $cmdParts[] = '--postprocessor-args "ffmpeg:-c:v copy -c:a aac -b:a 192k"';
    } else {
        $cmdParts[] = '-x';
        $cmdParts[] = '--audio-format ' . escapeshellarg($audioFormat);
        $cmdParts[] = '--audio-quality 0';
    }
    
    $cmdParts[] = '--no-mtime';
    $cmdParts[] = '--concurrent-fragments 5';

    if ($singleSong === 1) {
        $cmdParts[] = '--no-playlist';
    } else {
        $cmdParts[] = '--yes-playlist';
        if ($limit !== 'all') {
            $cmdParts[] = '--max-downloads ' . escapeshellarg($limit);
        }
    }

    if ($metadata === 1) {
        $cmdParts[] = '--embed-metadata';
        $cmdParts[] = '--embed-thumbnail';
    }

    $cmdParts[] = '-o "' . $saveDir . DIRECTORY_SEPARATOR . '%(title)s.%(ext)s"';
    
    $urls = explode(',', $url);
    foreach ($urls as $u) {
        $u = trim($u);
        if (!empty($u)) {
            $cmdParts[] = escapeshellarg($u);
        }
    }

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
                $safeTargetDir = $saveDir;
                if (substr($safeTargetDir, -1) === '\\' || substr($safeTargetDir, -1) === '/') {
                    $safeTargetDir .= '\\';
                }
                $cmdMix = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe -NoProfile -ExecutionPolicy Bypass -File "' . $workDir . DIRECTORY_SEPARATOR . 'mezclar_musica.ps1" -TargetDir "' . $safeTargetDir . '" 2>&1';
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
