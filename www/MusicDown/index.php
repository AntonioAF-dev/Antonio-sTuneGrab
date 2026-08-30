<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Antonio's TuneGrab - Multimedia Downloader & Player</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <!-- jsmediatags para lectura de ID3 y carátulas bajo demanda (Lazy Loading) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jsmediatags/3.9.5/jsmediatags.min.js"></script>
</head>
<body>

  <!-- Overlay Drag & Drop Global -->
  <div class="drag-drop-overlay" id="dragDropOverlay">
    <div class="drag-drop-message">
      <i class="fas fa-cloud-arrow-down"></i>
      <h3>Suelta el enlace o texto aquí</h3>
      <p>Se agregará automáticamente a la barra de búsqueda</p>
    </div>
  </div>

  <div class="app-wrapper">
    
    <!-- Encabezado de la Aplicación -->
    <header class="header">
      <div class="brand">
        <div class="brand-logo">
          <i class="fas fa-compact-disc fa-spin-pulse"></i>
        </div>
        <div class="brand-text">
          <h1>Antonio's TuneGrab</h1>
          <p>Reproductor y Descargador Multimedia</p>
        </div>
      </div>

      <div class="header-actions">
        <!-- Badges de estado de dependencias -->
        <div class="status-badges" id="statusIndicators">
          <div class="badge warn" id="ytdlpBadge" title="Estado de yt-dlp">
            <span class="badge-dot"></span>
            <span class="badge-text">yt-dlp: Verificando...</span>
          </div>
          <div class="badge warn" id="ffmpegBadge" title="Estado de FFmpeg">
            <span class="badge-dot"></span>
            <span class="badge-text">FFmpeg: Verificando...</span>
          </div>
        </div>

        <button type="button" class="btn-icon" id="checkDepsBtn" title="Verificar / Instalar Motores (yt-dlp/FFmpeg)">
          <i class="fas fa-wrench"></i>
        </button>

        <!-- Toggle Modo Claro / Oscuro -->
        <button type="button" class="btn-icon" id="themeToggleBtn" title="Alternar Tema Claro / Oscuro">
          <i class="fas fa-moon" id="themeToggleIcon"></i>
        </button>
      </div>
    </header>

    <!-- Grid Principal Estricto de 3 Columnas (Single Screen, Sin Scroll Global) -->
    <main class="main-layout" id="mainLayout">

      <!-- COLUMNA 1 (Izquierda: 350px): Búsqueda, Ajustes Expandidos & Herramientas -->
      <aside class="sidebar-column">
        
        <!-- CARD 1: Búsqueda y Descarga Rápida -->
        <section class="card card-download">
          <h2 class="card-title">
            <i class="fas fa-cloud-arrow-down"></i> Descargar Contenido
          </h2>

          <div class="form-group">
            <label class="form-label" for="urlInput">
              <span>Búsqueda o Enlace</span>
              <div class="label-actions">
                <button type="button" class="btn-chip" id="pasteBtn" title="Pegar del portapapeles">
                  <i class="fas fa-paste"></i> Pegar
                </button>
                <button type="button" class="btn-chip" id="clearBtn" title="Limpiar caja de texto">
                  <i class="fas fa-xmark"></i>
                </button>
              </div>
            </label>
            <div class="input-wrapper search-box-wrapper">
              <i class="fas fa-link input-icon"></i>
              <input type="text" id="urlInput" class="text-input" placeholder="Pega un enlace o escribe 'Queen Hits'..." autocomplete="off" spellcheck="false">
              <button type="button" id="searchBtn" class="search-inside-btn" title="Buscar en YouTube">
                <i class="fas fa-magnifying-glass"></i>
              </button>
            </div>
          </div>

          <!-- Botón de Descarga Principal -->
          <button type="button" class="btn btn-primary btn-hero" id="downloadBtn" title="Inicia la descarga">
            <i class="fas fa-arrow-down-to-line"></i> Iniciar Descarga
          </button>
        </section>

        <!-- CARD 2: Ajustes de Descarga (Siempre Visible y Expandido) -->
        <section class="card card-settings">
          <h2 class="card-title">
            <i class="fas fa-sliders"></i> Ajustes de Descarga
          </h2>

          <!-- Ubicación de Descarga -->
          <div class="form-group">
            <label class="form-label" for="saveDirInput">
              <span>Carpeta de Destino</span>
              <button type="button" class="btn-chip" id="browseDirBtn" title="Seleccionar carpeta">
                <i class="fas fa-folder-open"></i> Examinar...
              </button>
            </label>
            <div class="input-wrapper">
              <i class="fas fa-folder input-icon"></i>
              <input type="text" id="saveDirInput" class="text-input" placeholder="Ruta de carpeta..." autocomplete="off" spellcheck="false" value="C:\Descargas_MusicDown">
            </div>
          </div>

          <!-- Formato y Límite de Playlist -->
          <div class="options-grid">
            <div class="form-group">
              <label class="form-label" for="audioFormatSelect">Formato</label>
              <select id="audioFormatSelect" class="select-input">
                <option value="mp3" selected>MP3 (Universal)</option>
                <option value="m4a">M4A (AAC Alta Calidad)</option>
                <option value="flac">FLAC (Sin Pérdida)</option>
                <option value="wav">WAV (Audio Puro)</option>
                <option value="mp4">MP4 (Video Completo)</option>
              </select>
            </div>

            <div class="form-group" id="limitGroup">
              <label class="form-label" for="songLimitSelect">Límite Playlist</label>
              <select id="songLimitSelect" class="select-input">
                <option value="1">1 (Primer tema)</option>
                <option value="10" selected>10 Temas</option>
                <option value="20">20 Temas</option>
                <option value="30">30 Temas</option>
                <option value="50">50 Temas</option>
                <option value="100">100 Temas</option>
                <option value="all">Todas (Ilimitado)</option>
                <option value="custom">Personalizado...</option>
              </select>
            </div>
          </div>

          <!-- Input límite personalizado -->
          <div class="custom-limit-box" id="customLimitBox">
            <div class="input-wrapper">
              <i class="fas fa-list-ol input-icon"></i>
              <input type="number" id="customLimitInput" class="text-input" placeholder="Cantidad exacta (ej: 15, 75)" min="1" max="1000">
            </div>
          </div>

          <!-- Switches Toggles -->
          <div class="toggles-container">
            <label class="toggle-switch">
              <input type="checkbox" id="singleSongCheck" checked>
              <div class="toggle-slider"></div>
              <span class="toggle-label">Descargar solo esta canción</span>
            </label>

            <label class="toggle-switch">
              <input type="checkbox" id="metadataCheck" checked>
              <div class="toggle-slider"></div>
              <span class="toggle-label">Incrustar Carátula y Metadatos</span>
            </label>

            <label class="toggle-switch">
              <input type="checkbox" id="mixAfterCheck">
              <div class="toggle-slider"></div>
              <span class="toggle-label">Mezclar canciones al terminar</span>
            </label>
          </div>
        </section>

        <!-- CARD 3: Herramientas USB / Carpeta -->
        <section class="card card-tools">
          <h2 class="card-title">
            <i class="fas fa-toolbox"></i> Herramientas
          </h2>
          <div class="tools-grid">
            <button type="button" class="btn btn-secondary btn-tool" id="mixBtn" title="Mezclar y reordenar aleatoriamente los archivos para USB">
              <i class="fas fa-shuffle"></i> Mezclar USB
            </button>
            <button type="button" class="btn btn-secondary btn-tool" id="cleanBtn" title="Eliminar descargas temporales y duplicados">
              <i class="fas fa-broom"></i> Limpiar USB
            </button>
            <button type="button" class="btn btn-secondary btn-tool" id="openFolderBtn" title="Abrir carpeta de descargas en el explorador">
              <i class="fas fa-folder-open"></i> Abrir Carpeta
            </button>
          </div>
        </section>

      </aside>

      <!-- COLUMNA 2 (Centro: Flexible 1fr): Lista de Canciones en Carpeta -->
      <section class="library-column card">
        
        <!-- Cabecera de la Biblioteca -->
        <div class="library-header">
          <div class="library-title-group">
            <div class="library-title">
              <i class="fas fa-music"></i>
              <h2>Canciones en Carpeta</h2>
            </div>
            <span class="track-count-badge" id="trackCountBadge">0 pistas</span>
          </div>

          <div class="library-toolbar">
            <div class="library-search-wrapper">
              <i class="fas fa-search library-search-icon"></i>
              <input type="text" id="librarySearchInput" class="library-search-input" placeholder="Filtrar por nombre...">
            </div>
            <button type="button" class="btn-icon" id="refreshFilesBtn" title="Actualizar lista de archivos">
              <i class="fas fa-rotate"></i>
            </button>
          </div>
        </div>

        <!-- Contenedor de Descarga en Progreso (Feedback Visual Acelerado) -->
        <div class="active-download-card" id="activeDownloadCard" style="display: none;">
          <div class="download-info-row">
            <div class="download-visual-badge">
              <i class="fas fa-arrow-down fa-bounce"></i>
            </div>
            <div class="download-text-group">
              <span class="download-status-title" id="downloadStatusTitle">Descargando...</span>
              <span class="download-status-detail" id="downloadStatusDetail">Conectando con YouTube...</span>
            </div>
            <span class="download-pct-badge" id="downloadPctBadge">0%</span>
          </div>
          <div class="download-progress-track">
            <div class="download-progress-fill" id="downloadProgressFill"></div>
          </div>
        </div>

        <!-- Lista scrolleable de canciones con corte elíptico -->
        <div class="files-list-container">
          <div class="files-list" id="filesList">
            <!-- Canciones inyectadas dinámicamente -->
          </div>
        </div>

      </section>

      <!-- COLUMNA 3 (Derecha: 350px): Mitad Superior (Detalles) & Mitad Inferior (Terminal en Vivo) -->
      <aside class="right-panel-column">
        
        <!-- Mitad Superior: Carátula HD & Metadatos -->
        <section class="card now-playing-card">
          <div class="panel-section-header">
            <div class="panel-section-title">
              <i class="fas fa-compact-disc"></i>
              <h2>Detalles de Reproducción</h2>
            </div>
          </div>

          <div class="hero-art-container">
            <!-- Reproductor de Audio: Vinilo Giratorio -->
            <div class="vinyl-record" id="vinylRecord">
              <div class="vinyl-grooves"></div>
              <div class="vinyl-center">
                <img id="sideArtImg" class="side-art-img" src="" alt="Album Art" style="display: none;">
                <div class="side-art-fallback" id="sideArtFallback">
                  <i class="fas fa-music"></i>
                </div>
              </div>
            </div>

            <!-- Reproductor de Video Integrado (Para archivos MP4 / WebM) -->
            <div class="video-player-wrapper" id="videoPlayerWrapper" style="display: none;">
              <video id="mainVideo" class="main-video-player" playsinline></video>
              <div class="video-overlay-controls">
                <button type="button" class="video-ctrl-btn" id="videoFullscreenBtn" title="Pantalla Completa">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="hero-track-info">
            <h3 id="sideTrackTitle" title="Selecciona una canción">Selecciona una pista</h3>
            <p id="sideTrackArtist">Antonio's TuneGrab</p>
          </div>

          <div class="extended-meta-card">
            <div class="meta-row">
              <span class="meta-label"><i class="fas fa-file-audio"></i> Formato</span>
              <span class="meta-value" id="sideMetaFormat">MP3</span>
            </div>
            <div class="meta-row">
              <span class="meta-label"><i class="fas fa-hard-drive"></i> Tamaño</span>
              <span class="meta-value" id="sideMetaSize">-- MB</span>
            </div>
            <div class="meta-row">
              <span class="meta-label"><i class="fas fa-clock"></i> Duración</span>
              <span class="meta-value" id="sideMetaDuration">0:00 / 0:00</span>
            </div>
            <div class="meta-row">
              <span class="meta-label"><i class="fas fa-sliders"></i> Estado</span>
              <span class="meta-value meta-status" id="sideMetaStatus">Listo</span>
            </div>
          </div>
        </section>

        <!-- Mitad Inferior: Terminal en Vivo Fija (Max 200px, Fondo Oscuro, Fuente Mono, Texto Verde) -->
        <section class="card terminal-card">
          <div class="terminal-card-header">
            <div class="terminal-card-title">
              <div class="terminal-dots">
                <span class="terminal-dot dot-red"></span>
                <span class="terminal-dot dot-yellow"></span>
                <span class="terminal-dot dot-green"></span>
              </div>
              <span><i class="fas fa-terminal"></i> Terminal en Vivo</span>
            </div>
            <button type="button" class="btn-chip" id="clearTerminalBtn" title="Limpiar salida">
              <i class="fas fa-broom"></i>
            </button>
          </div>
          <div class="terminal-body" id="terminalBody">
            <div class="log-line log-dim">[Sistema] Listo. Busca una canción o pega un enlace para descargar.</div>
          </div>
        </section>

      </aside>

    </main>

  </div>

  <!-- BARRA DE REPRODUCCIÓN INFERIOR FIJA (Max-width 1920px Centrada, Sin Backdrop-Filter) -->
  <footer class="player-bar" id="playerBar">
    <div class="player-bar-inner">
      
      <!-- Sección Izquierda: Carátula y Metadatos -->
      <div class="player-meta-section">
        <div class="player-cover-box" id="playerCoverBox">
          <img id="playerCoverImg" class="player-cover-img" src="" alt="Album Cover" style="display: none;">
          <div class="player-cover-fallback" id="playerCoverFallback">
            <i class="fas fa-music"></i>
          </div>
        </div>
        <div class="player-info-box">
          <div class="player-track-title" id="audioTitle" title="Selecciona una canción">Selecciona una canción</div>
          <div class="player-track-artist" id="audioArtist">Antonio's TuneGrab</div>
        </div>
      </div>

      <!-- Sección Central: Controles y Barra Interactiva -->
      <div class="player-controls-section">
        <div class="player-buttons-row">
          <button type="button" class="player-btn-sub" id="shuffleBtn" title="Modo Aleatorio: Desactivado">
            <i class="fas fa-shuffle"></i>
          </button>
          <button type="button" class="player-btn-sub" id="prevBtn" title="Pista Anterior">
            <i class="fas fa-backward-step"></i>
          </button>
          <button type="button" class="player-btn-main" id="audioPlayBtn" title="Reproducir / Pausar">
            <i class="fas fa-play"></i>
          </button>
          <button type="button" class="player-btn-sub" id="nextBtn" title="Siguiente Pista">
            <i class="fas fa-forward-step"></i>
          </button>
          <button type="button" class="player-btn-sub" id="repeatBtn" title="Modo Repetición: Desactivado">
            <i class="fas fa-repeat"></i>
          </button>
        </div>

        <div class="player-timeline-row">
          <span class="time-label" id="currentTimeDisplay">0:00</span>
          <div class="slider-track-container">
            <input type="range" class="custom-slider audio-slider" id="audioScrubber" min="0" max="100" value="0" step="0.1">
          </div>
          <span class="time-label" id="totalTimeDisplay">0:00</span>
        </div>
      </div>

      <!-- Sección Derecha: Estado Rápido y Volumen -->
      <div class="player-actions-section">
        <div class="mini-status-chip" id="miniStatusChip" title="Estado de la aplicación">
          <span class="status-pulse-dot" id="statusPulseDot"></span>
          <span class="mini-status-text" id="miniStatusText">Listo</span>
        </div>

        <div class="volume-box">
          <button type="button" class="volume-icon-btn" id="volumeIconBtn" title="Silenciar / Activar sonido">
            <i class="fas fa-volume-high" id="volumeIcon"></i>
          </button>
          <input type="range" class="custom-slider volume-slider" id="volumeSlider" min="0" max="1" step="0.01" value="0.7">
        </div>
      </div>

    </div>

    <!-- Audio Element HTML5 Oculto -->
    <audio id="mainAudio" preload="metadata"></audio>
  </footer>

  <!-- MODAL DE RESULTADOS DE BÚSQUEDA DE YOUTUBE -->
  <div class="modal-overlay" id="searchModalOverlay">
    <div class="modal-window search-modal">
      <div class="modal-header">
        <div class="modal-title-group">
          <h3><i class="fas fa-magnifying-glass"></i> Resultados de Búsqueda</h3>
        </div>
        <button type="button" class="modal-close-btn" id="searchModalClose">
          <i class="fas fa-xmark"></i>
        </button>
      </div>
      <div class="modal-body search-modal-body" id="searchResultsContainer">
        <!-- Resultados inyectados aquí -->
      </div>
      <div class="modal-footer search-modal-footer">
        <button type="button" class="btn btn-secondary" id="selectAllBtn">
          <i class="fas fa-check-square"></i> Seleccionar Todos
        </button>
        <button type="button" class="btn btn-primary" id="downloadSelectedBtn" disabled>
          <i class="fas fa-download"></i> Descargar Seleccionadas (0)
        </button>
      </div>
    </div>
  </div>

  <!-- MENÚ CONTEXTUAL PERSONALIZADO (CLIC DERECHO EN CANCIÓN) -->
  <div class="custom-context-menu" id="customContextMenu" style="display: none;">
    <div class="context-menu-item" id="ctxPlay">
      <i class="fas fa-play"></i> <span>Reproducir</span>
    </div>
    <div class="context-menu-item" id="ctxOpenLocation">
      <i class="fas fa-folder-open"></i> <span>Abrir ubicación en Explorador</span>
    </div>
    <div class="context-menu-item" id="ctxRename">
      <i class="fas fa-pen-to-square"></i> <span>Renombrar archivo</span>
    </div>
    <div class="context-menu-divider"></div>
    <div class="context-menu-item context-danger" id="ctxDelete">
      <i class="fas fa-trash"></i> <span>Eliminar archivo</span>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/app.js"></script>
</body>
</html>
