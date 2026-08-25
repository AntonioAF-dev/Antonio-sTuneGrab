<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Antonio's TuneGrab - Descargador de Playlists y Música</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="container">
    
    <!-- Encabezado de la Aplicación -->
    <header class="header">
      <div class="brand">
        <div class="brand-logo">
          <i class="fas fa-compact-disc fa-spin-pulse"></i>
        </div>
        <div class="brand-text">
          <h1>Antonio's TuneGrab</h1>
          <p>Descargador de Playlists y Música de YouTube</p>
        </div>
      </div>

      <div class="status-badges">
        <button type="button" class="btn-icon" id="checkDepsBtn" title="Verificar Motores (yt-dlp/FFmpeg)">
          <i class="fas fa-wrench"></i>
        </button>
        <div class="badge warn" id="ytdlpBadge">
          <span class="badge-dot"></span>
          <span class="badge-text">yt-dlp: Verificando...</span>
        </div>
        <div class="badge warn" id="ffmpegBadge">
          <span class="badge-dot"></span>
          <span class="badge-text">FFmpeg: Verificando...</span>
        </div>
      </div>
    </header>

    <!-- Grid Principal de la Interfaz -->
    <main class="main-grid">

      <!-- Panel Izquierdo: Configuración de Descarga -->
      <section class="card">
        <h2 class="card-title">
          <i class="fas fa-sliders"></i> Configuración de Descarga
        </h2>

        <!-- Entrada de URL -->
        <div class="form-group">
          <label class="form-label" for="urlInput">
            <span>Enlace / URL de la Playlist o Canción</span>
            <div style="display: flex; gap: 6px;">
              <button type="button" class="btn-secondary" id="pasteBtn" style="padding: 2px 8px; font-size: 0.75rem;" title="Pegar del portapapeles">
                <i class="fas fa-paste"></i> Pegar
              </button>
              <button type="button" class="btn-secondary" id="clearBtn" style="padding: 2px 8px; font-size: 0.75rem;" title="Limpiar">
                <i class="fas fa-xmark"></i>
              </button>
            </div>
          </label>
          <div class="input-wrapper">
            <i class="fas fa-link input-icon"></i>
            <input type="text" id="urlInput" class="text-input" placeholder="https://www.youtube.com/watch?v=... o playlist URL" autocomplete="off" spellcheck="false">
          </div>
        </div>

        <!-- Ubicación de Descarga -->
        <div class="form-group">
          <label class="form-label" for="saveDirInput">
            <span>Ubicación de Descarga</span>
            <button type="button" class="btn-secondary" id="browseDirBtn" style="padding: 2px 8px; font-size: 0.75rem;" title="Examinar carpeta">
              <i class="fas fa-folder-open"></i> Examinar...
            </button>
          </label>
          <div class="input-wrapper">
            <i class="fas fa-folder input-icon"></i>
            <input type="text" id="saveDirInput" class="text-input" placeholder="Ruta de la carpeta..." autocomplete="off" spellcheck="false" value="C:\Descargas_MusicDown">
          </div>
                <!-- Selector de Límite de Canciones -->
        <div class="form-group" id="limitGroup">
          <label class="form-label" for="songLimitSelect">Límite de Canciones (Para Playlists)</label>
          <div class="options-row" style="grid-template-columns: 1fr;">
            <select id="songLimitSelect" class="select-input">
              <option value="1">1 (Solo primer tema)</option>
              <option value="10" selected>10 Temas</option>
              <option value="20">20 Temas</option>
              <option value="30">30 Temas</option>
              <option value="50">50 Temas</option>
              <option value="100">100 Temas</option>
              <option value="all">Todas (Ilimitado)</option>
              <option value="custom">Personalizado...</option>
            </select>
          </div>

          <!-- Input para Límite Personalizado -->
          <div class="custom-limit-box" id="customLimitBox">
            <div class="input-wrapper" style="width: 100%;">
              <i class="fas fa-list-ol input-icon"></i>
              <input type="number" id="customLimitInput" class="text-input" placeholder="Cantidad exacta (ej: 15, 75)" min="1" max="1000">
            </div>
          </div>
        </div>     </div>

        <!-- Formato de Audio y Opciones -->
        <div class="options-row">
          <div class="form-group">
            <label class="form-label" for="audioFormatSelect">Formato de Descarga</label>
            <select id="audioFormatSelect" class="select-input">
              <option value="mp3" selected>MP3 (Máxima Compatibilidad)</option>
              <option value="m4a">M4A (Alta Calidad AAC)</option>
              <option value="flac">FLAC (Sin Pérdida / Lossless)</option>
              <option value="wav">WAV (Audio Puro)</option>
              <option value="mp4">MP4 (Video para CarPlay/Autoradio)</option>
            </select>
          </div>

          <div class="form-group" style="justify-content: flex-end; display: flex; flex-direction: column; gap: 10px;">
            <label class="toggle-switch">
              <input type="checkbox" id="singleSongCheck" checked>
              <div class="toggle-slider"></div>
              <span class="toggle-label">Descargar solo esta canción</span>
            </label>
            <label class="toggle-switch">
              <input type="checkbox" id="mixAfterCheck">
              <div class="toggle-slider"></div>
              <span class="toggle-label">Mezclar automáticamente</span>
            </label>
          </div>
        </div>

        <!-- Botones de Acción Principal -->
        <div class="btn-group">
          <button type="button" class="btn btn-primary" id="downloadBtn">
            <i class="fas fa-download"></i> Iniciar Descarga
          </button>
          
          <div class="btn-row">
            <button type="button" class="btn btn-secondary" id="mixBtn">
              <i class="fas fa-shuffle"></i> Mezclar
            </button>
            <button type="button" class="btn btn-secondary" id="openFolderBtn">
              <i class="fas fa-folder-open"></i> Abrir
            </button>
          </div>
        </div>

      </section>

      <!-- Panel Derecho: Consola Terminal en Vivo & Biblioteca -->
      <section class="card terminal-card">
        
        <!-- Cabecera de la Consola -->
        <div class="terminal-header">
          <div class="terminal-dots">
            <span class="terminal-dot dot-red"></span>
            <span class="terminal-dot dot-yellow"></span>
            <span class="terminal-dot dot-green"></span>
          </div>
          <div class="terminal-title">
            <i class="fas fa-terminal"></i> Terminal de Salida en Vivo
          </div>
          <button type="button" class="btn-secondary" id="clearTerminalBtn" style="padding: 2px 8px; font-size: 0.7rem;">
            Limpiar
          </button>
        </div>

        <!-- Cuerpo del Terminal -->
        <div class="terminal-body" id="terminalBody">
          <div class="log-line log-dim">[Sistema] Listo para iniciar descarga. Ingresa la URL arriba y presiona "Iniciar Descarga".</div>
        </div>

        <!-- Barra de Progreso -->
        <div class="progress-container" id="progressContainer" style="padding: 12px 16px 4px 16px;">
          <div class="progress-info">
            <span id="progressStatusText">Procesando...</span>
            <span id="progressPercentText">0%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="progressBarFill"></div>
          </div>
        </div>

        <!-- Lista de Canciones Descargadas -->
        <div style="padding: 12px 16px; border-top: 1px solid var(--border-card); background: rgba(255, 255, 255, 0.4); display: flex; flex-direction: column; flex: 0.8; min-height: 120px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-shrink: 0;">
            <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
              <i class="fas fa-music"></i> Canciones en Carpeta Actual
            </span>
            <button type="button" class="btn-secondary" id="refreshFilesBtn" style="padding: 2px 8px; font-size: 0.75rem;">
              <i class="fas fa-rotate"></i>
            </button>
          </div>
          <div class="files-list" id="filesList">
            <div class="empty-state">Cargando archivos...</div>
          </div>
        </div>

      </section>

    </main>

    <!-- Footer / Créditos -->
    <footer style="text-align: center; margin-top: auto; padding-bottom: 4px; font-size: 0.8rem; color: var(--text-muted); flex-shrink: 0;">
      <p>
        Impulsado por la tecnología de 
        <a href="https://github.com/cztomczak/phpdesktop" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: bold;">PHPDesktop</a> y 
        <a href="https://github.com/yt-dlp/yt-dlp" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: bold;">yt-dlp</a>.
      </p>
      <p style="margin-top: 0.3rem;">
        Integración y desarrollo de interfaz por <a href="https://github.com/AntonioAF-dev" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: bold;">AntonioAF-dev</a>
      </p>
    </footer>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/app.js"></script>
</body>
</html>
