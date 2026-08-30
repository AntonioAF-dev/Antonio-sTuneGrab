document.addEventListener('DOMContentLoaded', () => {
  // ============================================================
  // ELEMENTOS DEL DOM
  // ============================================================

  // Búsqueda y Descarga
  const urlInput = document.getElementById('urlInput');
  const pasteBtn = document.getElementById('pasteBtn');
  const clearBtn = document.getElementById('clearBtn');
  const searchBtn = document.getElementById('searchBtn');
  const downloadBtn = document.getElementById('downloadBtn');

  // Ajustes de Descarga
  const saveDirInput = document.getElementById('saveDirInput');
  const browseDirBtn = document.getElementById('browseDirBtn');
  const audioFormatSelect = document.getElementById('audioFormatSelect');
  const songLimitSelect = document.getElementById('songLimitSelect');
  const customLimitBox = document.getElementById('customLimitBox');
  const customLimitInput = document.getElementById('customLimitInput');
  const singleSongCheck = document.getElementById('singleSongCheck');
  const metadataCheck = document.getElementById('metadataCheck');
  const mixAfterCheck = document.getElementById('mixAfterCheck');

  // Herramientas USB / Carpeta
  const mixBtn = document.getElementById('mixBtn');
  const cleanBtn = document.getElementById('cleanBtn');
  const openFolderBtn = document.getElementById('openFolderBtn');

  // Biblioteca / Lista de Archivos
  const filesList = document.getElementById('filesList');
  const trackCountBadge = document.getElementById('trackCountBadge');
  const librarySearchInput = document.getElementById('librarySearchInput');
  const refreshFilesBtn = document.getElementById('refreshFilesBtn');

  // Feedback Visual de Descarga
  const activeDownloadCard = document.getElementById('activeDownloadCard');
  const downloadStatusTitle = document.getElementById('downloadStatusTitle');
  const downloadStatusDetail = document.getElementById('downloadStatusDetail');
  const downloadPctBadge = document.getElementById('downloadPctBadge');
  const downloadProgressFill = document.getElementById('downloadProgressFill');

  // Barra de Reproducción Inferior Fija
  const mainAudio = document.getElementById('mainAudio');
  const playerCoverImg = document.getElementById('playerCoverImg');
  const playerCoverFallback = document.getElementById('playerCoverFallback');
  const audioTitle = document.getElementById('audioTitle');
  const audioArtist = document.getElementById('audioArtist');

  const audioPlayBtn = document.getElementById('audioPlayBtn');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const shuffleBtn = document.getElementById('shuffleBtn');
  const repeatBtn = document.getElementById('repeatBtn');

  const audioScrubber = document.getElementById('audioScrubber');
  const currentTimeDisplay = document.getElementById('currentTimeDisplay');
  const totalTimeDisplay = document.getElementById('totalTimeDisplay');

  const miniStatusChip = document.getElementById('miniStatusChip');
  const miniStatusText = document.getElementById('miniStatusText');
  const volumeIconBtn = document.getElementById('volumeIconBtn');
  const volumeIcon = document.getElementById('volumeIcon');
  const volumeSlider = document.getElementById('volumeSlider');

  // Columna 3 (Derecha): Detalles de Reproducción & Terminal en Vivo
  const vinylRecord = document.getElementById('vinylRecord');
  const videoPlayerWrapper = document.getElementById('videoPlayerWrapper');
  const mainVideo = document.getElementById('mainVideo');
  const videoFullscreenBtn = document.getElementById('videoFullscreenBtn');
  const sideArtImg = document.getElementById('sideArtImg');
  const sideArtFallback = document.getElementById('sideArtFallback');
  const sideTrackTitle = document.getElementById('sideTrackTitle');
  const sideTrackArtist = document.getElementById('sideTrackArtist');
  const sideMetaFormat = document.getElementById('sideMetaFormat');
  const sideMetaSize = document.getElementById('sideMetaSize');
  const sideMetaDuration = document.getElementById('sideMetaDuration');
  const sideMetaStatus = document.getElementById('sideMetaStatus');

  const terminalBody = document.getElementById('terminalBody');
  const clearTerminalBtn = document.getElementById('clearTerminalBtn');

  // Modal de Búsqueda de YouTube
  const searchModalOverlay = document.getElementById('searchModalOverlay');
  const searchModalClose = document.getElementById('searchModalClose');
  const searchResultsContainer = document.getElementById('searchResultsContainer');
  const selectAllBtn = document.getElementById('selectAllBtn');
  const downloadSelectedBtn = document.getElementById('downloadSelectedBtn');

  // Menú Contextual Personalizado
  const customContextMenu = document.getElementById('customContextMenu');
  const ctxPlay = document.getElementById('ctxPlay');
  const ctxOpenLocation = document.getElementById('ctxOpenLocation');
  const ctxRename = document.getElementById('ctxRename');
  const ctxDelete = document.getElementById('ctxDelete');

  // Drag & Drop y Tema
  const dragDropOverlay = document.getElementById('dragDropOverlay');
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  const themeToggleIcon = document.getElementById('themeToggleIcon');
  const checkDepsBtn = document.getElementById('checkDepsBtn');
  const statusIndicators = document.getElementById('statusIndicators');
  const ytdlpBadge = document.getElementById('ytdlpBadge');
  const ffmpegBadge = document.getElementById('ffmpegBadge');

  // ============================================================
  // ESTADO GLOBAL & OPTIMIZACIÓN
  // ============================================================
  let currentPlaylist = [];
  let filteredPlaylist = [];
  let currentSongIndex = -1;
  let isShuffle = false;
  let repeatMode = 'off';
  let isSeeking = false;
  let currentEventSource = null;
  let contextTargetFileName = '';

  // Performance throttling vars
  let lastSecond = -1;
  let rafPending = false;
  let lastProgressUpdate = 0;
  let lastLoggedMsg = '';
  const MAX_TERMINAL_LINES = 80;

  // Cache de carátulas ID3
  const coverArtCache = new Map();

  // ============================================================
  // 1. GESTOR DE TEMA (MODO CLARO POR DEFECTO / OSCURO)
  // ============================================================
  function initTheme() {
    // Inicia en Modo Claro por defecto
    const savedTheme = localStorage.getItem('antonio_theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
  }

  function updateThemeIcon(theme) {
    if (!themeToggleIcon) return;
    if (theme === 'dark') {
      themeToggleIcon.className = 'fas fa-sun';
      themeToggleBtn.title = 'Cambiar a Modo Claro';
    } else {
      themeToggleIcon.className = 'fas fa-moon';
      themeToggleBtn.title = 'Cambiar a Modo Oscuro';
    }
  }

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('antonio_theme', newTheme);
      updateThemeIcon(newTheme);
    });
  }

  // ============================================================
  // 2. LÍMITE DE CANCIONES (PLAYLISTS)
  // ============================================================
  function updateLimitUI() {
    const isCustom = songLimitSelect && songLimitSelect.value === 'custom';
    if (customLimitBox) {
      customLimitBox.classList.toggle('active', isCustom);
    }
  }
  if (songLimitSelect) songLimitSelect.addEventListener('change', updateLimitUI);

  // ============================================================
  // 3. VERIFICACIÓN DE DEPENDENCIAS (YT-DLP & FFMPEG)
  // ============================================================
  async function checkDependencies() {
    try {
      const res = await fetch('api.php?action=status');
      const data = await res.json();
      
      if (data.ytdlp && data.ffmpeg) {
        if (statusIndicators) statusIndicators.style.display = 'none';
      } else {
        if (statusIndicators) statusIndicators.style.display = 'flex';
        
        if (ytdlpBadge) {
          ytdlpBadge.className = data.ytdlp ? 'badge ok' : 'badge error';
          ytdlpBadge.querySelector('.badge-text').textContent = data.ytdlp ? 'yt-dlp: Listo' : 'yt-dlp: Falta';
        }
        
        if (ffmpegBadge) {
          ffmpegBadge.className = data.ffmpeg ? 'badge ok' : 'badge error';
          ffmpegBadge.querySelector('.badge-text').textContent = data.ffmpeg ? 'FFmpeg: Listo' : 'FFmpeg: Falta';
        }
      }
    } catch (e) {
      console.warn('Error al verificar dependencias', e);
    }
  }

  if (checkDepsBtn) {
    checkDepsBtn.addEventListener('click', async () => {
      logToTerminal('info', 'Abriendo instalador de dependencias...');
      try {
        await fetch('api.php?action=install_deps');
        Swal.fire({
          title: 'Instalador de Motores',
          text: 'Se ha abierto una consola externa para verificar y descargar yt-dlp y FFmpeg.',
          icon: 'info',
          confirmButtonText: 'Entendido'
        });
      } catch (e) {
        logToTerminal('error', 'Error al abrir instalador.');
      }
    });
  }

  // ============================================================
  // 4. PORTAPAPELES Y SELECTOR DE CARPETA
  // ============================================================
  if (pasteBtn && urlInput) {
    pasteBtn.addEventListener('click', async () => {
      try {
        if (navigator.clipboard && navigator.clipboard.readText) {
          const text = await navigator.clipboard.readText();
          if (text) {
            urlInput.value = text.trim();
            urlInput.focus();
            return;
          }
        }
      } catch (err) {}
      
      try {
        const res = await fetch('api.php?action=get_clipboard');
        const data = await res.json();
        if (data.text) {
          urlInput.value = data.text.trim();
          urlInput.focus();
        } else {
          Swal.fire('Información', 'El portapapeles está vacío.', 'info');
        }
      } catch (e) {
        Swal.fire('Error', 'No se pudo leer el portapapeles.', 'error');
      }
    });
  }

  if (clearBtn && urlInput) {
    clearBtn.addEventListener('click', () => {
      urlInput.value = '';
      urlInput.focus();
    });
  }

  if (browseDirBtn && saveDirInput) {
    browseDirBtn.addEventListener('click', async () => {
      const orig = browseDirBtn.innerHTML;
      browseDirBtn.disabled = true;
      browseDirBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
      try {
        const res = await fetch('api.php?action=pick_folder');
        const data = await res.json();
        if (data.path && data.path.trim() !== '') {
          saveDirInput.value = data.path;
          loadFilesList();
        }
      } catch (err) {
        logToTerminal('error', 'Error al seleccionar carpeta: ' + err.message);
      } finally {
        browseDirBtn.disabled = false;
        browseDirBtn.innerHTML = orig;
      }
    });
  }

  // ============================================================
  // 5. REPRODUCTOR DE AUDIO ULTRA-OPTIMIZADO & LAZY LOADING
  // ============================================================

  function formatTime(seconds) {
    if (isNaN(seconds) || seconds === Infinity || seconds < 0) return '0:00';
    const m = (seconds / 60) | 0;
    const s = (seconds % 60) | 0;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
  }

  function parseTrackName(fileName) {
    const clean = fileName.replace(/\.[^/.]+$/, '');
    
    // Detectar prefijo correlativo (ej: "001 - ", "042. ", "15 - ")
    const prefixMatch = clean.match(/^(\d{1,4}[\s\._-]+)/);
    const prefix = prefixMatch ? prefixMatch[1] : '';
    const body = prefix ? clean.substring(prefix.length) : clean;

    let artist = "Desconocido";
    let songTitle = body;

    if (body.includes(' - ')) {
      const parts = body.split(' - ');
      artist = parts[0].trim();
      songTitle = parts.slice(1).join(' - ').trim();
    }

    // Título a mostrar preservando el número correlativo si existe
    const displayTitle = prefix ? `${prefix}${songTitle}` : songTitle;

    return {
      prefix: prefix,
      artist: artist,
      title: displayTitle,
      rawName: clean
    };
  }

  // LAZY LOADING DE METADATOS ID3 (Solo se ejecuta bajo demanda en la canción reproducida)
  async function extractID3Metadata(fileName, audioUrl) {
    if (coverArtCache.has(fileName)) {
      const cached = coverArtCache.get(fileName);
      applyTrackDisplay(cached.title, cached.artist, cached.coverUrl);
      return;
    }

    const fallback = parseTrackName(fileName);
    applyTrackDisplay(fallback.title, fallback.artist, null);

    if (window.jsmediatags && typeof window.jsmediatags.read === 'function') {
      try {
        window.jsmediatags.read(audioUrl, {
          onSuccess: (tag) => {
            const tags = tag.tags;
            let title = tags.title ? tags.title.trim() : fallback.title;
            let artist = tags.artist ? tags.artist.trim() : fallback.artist;
            let coverUrl = null;

            if (tags.picture) {
              const { data, format } = tags.picture;
              let base64String = "";
              const len = data.length;
              for (let i = 0; i < len; i++) {
                base64String += String.fromCharCode(data[i]);
              }
              coverUrl = `data:${format};base64,${window.btoa(base64String)}`;
            }

            coverArtCache.set(fileName, { title, artist, coverUrl });
            if (audioTitle.dataset.currentFile === fileName) {
              applyTrackDisplay(title, artist, coverUrl);
            }
          },
          onError: () => {
            coverArtCache.set(fileName, { title: fallback.title, artist: fallback.artist, coverUrl: null });
          }
        });
      } catch (err) {}
    }
  }

  function applyTrackDisplay(title, artist, coverUrl) {
    audioTitle.textContent = title;
    audioTitle.title = title;
    audioArtist.textContent = artist || "Antonio's TuneGrab";

    // Panel Derecho (Columna 3)
    if (sideTrackTitle) {
      sideTrackTitle.textContent = title;
      sideTrackTitle.title = title;
    }
    if (sideTrackArtist) {
      sideTrackArtist.textContent = artist || "Antonio's TuneGrab";
    }

    if (coverUrl) {
      playerCoverImg.src = coverUrl;
      playerCoverImg.style.display = 'block';
      playerCoverFallback.style.display = 'none';

      if (sideArtImg) {
        sideArtImg.src = coverUrl;
        sideArtImg.style.display = 'block';
      }
      if (sideArtFallback) sideArtFallback.style.display = 'none';
    } else {
      playerCoverImg.style.display = 'none';
      playerCoverImg.src = '';
      playerCoverFallback.style.display = 'flex';

      if (sideArtImg) {
        sideArtImg.style.display = 'none';
        sideArtImg.src = '';
      }
      if (sideArtFallback) sideArtFallback.style.display = 'flex';
    }
  }

  let currentMediaType = 'audio'; // 'audio' | 'video'

  function getActiveMedia() {
    return currentMediaType === 'video' && mainVideo ? mainVideo : mainAudio;
  }

  function playSong(fileName, explicitIndex = -1) {
    if (!fileName) return;

    const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
    const videoUrl = `api.php?action=play&file=${encodeURIComponent(fileName)}&save_dir=${encodeURIComponent(saveDir)}`;
    const audioStreamUrl = `api.php?action=video_audio&file=${encodeURIComponent(fileName)}&save_dir=${encodeURIComponent(saveDir)}`;
    const ext = fileName.split('.').pop().toLowerCase();
    const isVideo = ext === 'mp4' || ext === 'webm';
    currentMediaType = isVideo ? 'video' : 'audio';

    audioTitle.dataset.currentFile = fileName;
    lastSecond = -1;

    const vol = volumeSlider ? parseFloat(volumeSlider.value) : 0.7;

    if (isVideo && mainVideo) {
      if (vinylRecord) vinylRecord.style.display = 'none';
      if (videoPlayerWrapper) videoPlayerWrapper.style.display = 'flex';

      // En Chromium CEF, silenciar el tag <video> y reproducir el audio por <audio> garantiza 100% de compatibilidad sonora
      mainVideo.muted = true;
      mainVideo.src = videoUrl;

      mainAudio.src = audioStreamUrl;
      mainAudio.volume = vol;

      // Iniciar video y audio en sincronía
      Promise.all([
        mainVideo.play().catch(() => {}),
        mainAudio.play().catch(() => {})
      ]).then(() => {
        audioPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
        updateActiveTrackVisual(fileName, true);
        if (sideMetaStatus) sideMetaStatus.textContent = 'Reproduciendo Video';
      }).catch(() => {
        audioPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
      });
    } else {
      if (mainVideo) {
        mainVideo.pause();
        mainVideo.removeAttribute('src');
        mainVideo.load();
      }

      if (videoPlayerWrapper) videoPlayerWrapper.style.display = 'none';
      if (vinylRecord) vinylRecord.style.display = 'flex';

      mainAudio.src = videoUrl;
      mainAudio.volume = vol;

      mainAudio.play().then(() => {
        audioPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
        updateActiveTrackVisual(fileName, true);
        if (vinylRecord) vinylRecord.classList.add('spinning');
        if (sideMetaStatus) sideMetaStatus.textContent = 'Reproduciendo';
      }).catch(err => {
        audioPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
        if (vinylRecord) vinylRecord.classList.remove('spinning');
      });
    }

    if (explicitIndex >= 0) {
      currentSongIndex = explicitIndex;
    } else {
      currentSongIndex = currentPlaylist.findIndex(f => f.name === fileName);
    }

    const currentTrackObj = currentPlaylist.find(f => f.name === fileName);
    if (currentTrackObj) {
      if (sideMetaFormat) sideMetaFormat.textContent = isVideo ? `${currentTrackObj.ext.toUpperCase()} (Video)` : currentTrackObj.ext.toUpperCase();
      if (sideMetaSize) sideMetaSize.textContent = currentTrackObj.size;
    }

    // Lazy load ID3 tag bajo demanda
    extractID3Metadata(fileName, isVideo ? audioStreamUrl : videoUrl);
    updateMiniStatus(`Reproduciendo: ${fileName}`);
  }

  function updateActiveTrackVisual(fileName, isPlaying) {
    document.querySelectorAll('.file-item').forEach(item => {
      if (item.dataset.name === fileName) {
        item.classList.add('active');
        if (isPlaying) {
          item.classList.add('playing');
        } else {
          item.classList.remove('playing');
        }
      } else {
        item.classList.remove('active');
        item.classList.remove('playing');
      }
    });
  }

  // Play / Pause Toggle
  if (audioPlayBtn) {
    audioPlayBtn.addEventListener('click', () => {
      if (currentMediaType === 'video' && mainVideo) {
        if (!mainVideo.src && currentPlaylist.length > 0) {
          playSong(currentPlaylist[0].name, 0);
          return;
        }
        if (mainAudio.paused) {
          mainVideo.play().catch(() => {});
          mainAudio.play().catch(() => {});
          audioPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
          updateActiveTrackVisual(audioTitle.dataset.currentFile, true);
          if (sideMetaStatus) sideMetaStatus.textContent = 'Reproduciendo Video';
        } else {
          mainVideo.pause();
          mainAudio.pause();
          audioPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
          updateActiveTrackVisual(audioTitle.dataset.currentFile, false);
          if (sideMetaStatus) sideMetaStatus.textContent = 'Pausado';
        }
      } else {
        if (!mainAudio.src) {
          if (currentPlaylist.length > 0) {
            playSong(currentPlaylist[0].name, 0);
          }
          return;
        }

        if (mainAudio.paused) {
          mainAudio.play().then(() => {
            audioPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
            updateActiveTrackVisual(audioTitle.dataset.currentFile, true);
            if (vinylRecord) vinylRecord.classList.add('spinning');
            if (sideMetaStatus) sideMetaStatus.textContent = 'Reproduciendo';
          });
        } else {
          mainAudio.pause();
          audioPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
          updateActiveTrackVisual(audioTitle.dataset.currentFile, false);
          if (vinylRecord) vinylRecord.classList.remove('spinning');
          if (sideMetaStatus) sideMetaStatus.textContent = 'Pausado';
        }
      }
    });
  }

  // Click en el video para alternar play/pause o pantalla completa
  if (mainVideo) {
    mainVideo.addEventListener('click', () => {
      if (audioPlayBtn) audioPlayBtn.click();
    });
    mainVideo.addEventListener('dblclick', () => {
      toggleVideoFullscreen();
    });
  }

  if (videoFullscreenBtn) {
    videoFullscreenBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      toggleVideoFullscreen();
    });
  }

  function toggleVideoFullscreen() {
    if (!mainVideo) return;
    if (document.fullscreenElement) {
      if (document.exitFullscreen) document.exitFullscreen();
    } else {
      if (mainVideo.requestFullscreen) {
        mainVideo.requestFullscreen();
      } else if (mainVideo.webkitRequestFullscreen) {
        mainVideo.webkitRequestFullscreen();
      }
    }
  }

  function playNextTrack() {
    if (currentPlaylist.length === 0) return;

    if (isShuffle) {
      const nextIndex = Math.floor(Math.random() * currentPlaylist.length);
      playSong(currentPlaylist[nextIndex].name, nextIndex);
      return;
    }

    if (currentSongIndex < currentPlaylist.length - 1) {
      playSong(currentPlaylist[currentSongIndex + 1].name, currentSongIndex + 1);
    } else if (repeatMode === 'all') {
      playSong(currentPlaylist[0].name, 0);
    }
  }

  function playPrevTrack() {
    if (currentPlaylist.length === 0) return;

    const activeMedia = mainAudio;
    if (activeMedia.currentTime > 3) {
      activeMedia.currentTime = 0;
      if (currentMediaType === 'video' && mainVideo) mainVideo.currentTime = 0;
      return;
    }

    if (currentSongIndex > 0) {
      playSong(currentPlaylist[currentSongIndex - 1].name, currentSongIndex - 1);
    } else if (repeatMode === 'all') {
      playSong(currentPlaylist[currentPlaylist.length - 1].name, currentPlaylist.length - 1);
    }
  }

  if (nextBtn) nextBtn.addEventListener('click', playNextTrack);
  if (prevBtn) prevBtn.addEventListener('click', playPrevTrack);

  if (shuffleBtn) {
    shuffleBtn.addEventListener('click', () => {
      isShuffle = !isShuffle;
      shuffleBtn.classList.toggle('active', isShuffle);
      shuffleBtn.title = isShuffle ? 'Modo Aleatorio: Activado' : 'Modo Aleatorio: Desactivado';
    });
  }

  if (repeatBtn) {
    repeatBtn.addEventListener('click', () => {
      if (repeatMode === 'off') {
        repeatMode = 'all';
        repeatBtn.classList.add('active');
        repeatBtn.innerHTML = '<i class="fas fa-repeat"></i>';
        repeatBtn.title = 'Repetir: Todo';
      } else if (repeatMode === 'all') {
        repeatMode = 'one';
        repeatBtn.classList.add('active');
        repeatBtn.innerHTML = '<i class="fas fa-repeat-1"></i>';
        repeatBtn.title = 'Repetir: Esta canción';
      } else {
        repeatMode = 'off';
        repeatBtn.classList.remove('active');
        repeatBtn.innerHTML = '<i class="fas fa-repeat"></i>';
        repeatBtn.title = 'Repetir: Desactivado';
      }
    });
  }

  function onMediaEnded() {
    if (repeatMode === 'one') {
      mainAudio.currentTime = 0;
      if (currentMediaType === 'video' && mainVideo) mainVideo.currentTime = 0;
      mainAudio.play();
      if (currentMediaType === 'video' && mainVideo) mainVideo.play();
    } else {
      playNextTrack();
    }
  }

  mainAudio.addEventListener('ended', onMediaEnded);

  // THROTTLED TIMEUPDATE (1 actualización por segundo para texto + requestAnimationFrame para slider)
  function handleMediaTimeUpdate(media) {
    if (isSeeking || !media.duration) return;

    const curTime = media.currentTime;
    const dur = media.duration;
    const currentSec = curTime | 0;

    // Sincronización de video y audio si hay deriva mayor a 250ms
    if (currentMediaType === 'video' && mainVideo && !mainAudio.paused) {
      if (Math.abs(mainVideo.currentTime - mainAudio.currentTime) > 0.25) {
        mainVideo.currentTime = mainAudio.currentTime;
      }
    }

    // Actualizar texto SOLO 1 vez por segundo (Cero DOM thrashing)
    if (currentSec !== lastSecond) {
      lastSecond = currentSec;
      const curStr = formatTime(curTime);
      const totStr = formatTime(dur);
      currentTimeDisplay.textContent = curStr;
      totalTimeDisplay.textContent = totStr;

      if (sideMetaDuration) {
        sideMetaDuration.textContent = `${curStr} / ${totStr}`;
      }
    }

    // Actualizar slider vía requestAnimationFrame
    if (!rafPending) {
      rafPending = true;
      requestAnimationFrame(() => {
        rafPending = false;
        if (!isSeeking && media.duration) {
          audioScrubber.value = (media.currentTime / media.duration) * 100;
        }
      });
    }
  }

  mainAudio.addEventListener('timeupdate', () => handleMediaTimeUpdate(mainAudio));

  function handleMediaLoadedMetadata(media) {
    const totStr = formatTime(media.duration);
    totalTimeDisplay.textContent = totStr;
    if (sideMetaDuration) {
      sideMetaDuration.textContent = `0:00 / ${totStr}`;
    }
  }

  mainAudio.addEventListener('loadedmetadata', () => handleMediaLoadedMetadata(mainAudio));

  if (audioScrubber) {
    audioScrubber.addEventListener('mousedown', () => { isSeeking = true; });
    audioScrubber.addEventListener('touchstart', () => { isSeeking = true; });

    audioScrubber.addEventListener('input', () => {
      const activeMedia = mainAudio;
      if (activeMedia.duration) {
        const seekTime = (audioScrubber.value / 100) * activeMedia.duration;
        currentTimeDisplay.textContent = formatTime(seekTime);
      }
    });

    audioScrubber.addEventListener('change', () => {
      const activeMedia = mainAudio;
      if (activeMedia.duration) {
        const seekTime = (audioScrubber.value / 100) * activeMedia.duration;
        mainAudio.currentTime = seekTime;
        if (currentMediaType === 'video' && mainVideo) {
          mainVideo.currentTime = seekTime;
        }
      }
      isSeeking = false;
    });
  }

  // Volumen
  if (volumeSlider) {
    volumeSlider.addEventListener('input', () => {
      const vol = parseFloat(volumeSlider.value);
      mainAudio.volume = vol;
      updateVolumeIcon(vol);
    });
  }

  if (volumeIconBtn) {
    volumeIconBtn.addEventListener('click', () => {
      if (mainAudio.volume > 0) {
        volumeSlider.dataset.lastVol = mainAudio.volume;
        mainAudio.volume = 0;
        volumeSlider.value = 0;
      } else {
        const restoreVol = parseFloat(volumeSlider.dataset.lastVol) || 0.7;
        mainAudio.volume = restoreVol;
        volumeSlider.value = restoreVol;
      }
      updateVolumeIcon(mainAudio.volume);
    });
  }

  if (volumeIconBtn) {
    volumeIconBtn.addEventListener('click', () => {
      if (mainAudio.volume > 0) {
        volumeSlider.dataset.lastVol = mainAudio.volume;
        mainAudio.volume = 0;
        volumeSlider.value = 0;
      } else {
        const restoreVol = parseFloat(volumeSlider.dataset.lastVol) || 0.7;
        mainAudio.volume = restoreVol;
        volumeSlider.value = restoreVol;
      }
      updateVolumeIcon(mainAudio.volume);
    });
  }

  function updateVolumeIcon(vol) {
    if (!volumeIcon) return;
    if (vol === 0) {
      volumeIcon.className = 'fas fa-volume-xmark';
    } else if (vol < 0.5) {
      volumeIcon.className = 'fas fa-volume-low';
    } else {
      volumeIcon.className = 'fas fa-volume-high';
    }
  }

  // ============================================================
  // 6. GESTOR DE BIBLIOTECA (RENDERIZADO ULTRA-RÁPIDO)
  // ============================================================

  async function loadFilesList() {
    try {
      const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
      const res = await fetch(`api.php?action=list_files&save_dir=${encodeURIComponent(saveDir)}`);
      const data = await res.json();
      
      currentPlaylist = data.files || [];
      trackCountBadge.textContent = `${currentPlaylist.length} pistas`;
      
      renderLibraryList();
    } catch (e) {
      console.error('Error al cargar lista', e);
    }
  }

  function renderLibraryList() {
    const query = librarySearchInput ? librarySearchInput.value.trim().toLowerCase() : '';
    filteredPlaylist = currentPlaylist.filter(file => file.name.toLowerCase().includes(query));

    if (filteredPlaylist.length === 0) {
      filesList.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-compact-disc"></i>
          <span>${query ? 'No se encontraron canciones.' : 'No hay canciones en la carpeta actual.'}</span>
        </div>
      `;
      return;
    }

    const currentFile = audioTitle.dataset.currentFile || '';
    const isPlaying = !mainAudio.paused;

    // Renderizado por plantilla rápida (sin llamadas asíncronas bloqueantes)
    filesList.innerHTML = filteredPlaylist.map((file, idx) => {
      const parsed = parseTrackName(file.name);
      const isActive = file.name === currentFile;
      const iconClass = file.ext === 'mp4' ? 'fa-video' : 'fa-music';

      return `
        <div class="file-item ${isActive ? 'active' : ''} ${isActive && isPlaying ? 'playing' : ''}" data-name="${escapeHtml(file.name)}" data-index="${idx}">
          <div class="file-item-left">
            <div class="file-item-thumb">
              <i class="fas ${iconClass}"></i>
              <div class="equalizer-anim">
                <div class="equalizer-bar"></div>
                <div class="equalizer-bar"></div>
                <div class="equalizer-bar"></div>
              </div>
            </div>
            <div class="file-item-info">
              <div class="file-item-title" title="${escapeHtml(file.name)}">${escapeHtml(parsed.title)}</div>
              <div class="file-item-artist">${escapeHtml(parsed.artist)}</div>
            </div>
          </div>
          <div class="file-item-right">
            <span class="file-item-badge">${file.ext}</span>
            <span class="file-item-meta">${file.size}</span>
          </div>
        </div>
      `;
    }).join('');
  }

  function escapeHtml(str) {
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  if (librarySearchInput) {
    librarySearchInput.addEventListener('input', renderLibraryList);
  }

  if (refreshFilesBtn) {
    refreshFilesBtn.addEventListener('click', loadFilesList);
  }

  // Doble clic para reproducir
  filesList.addEventListener('dblclick', (e) => {
    const item = e.target.closest('.file-item');
    if (item && item.dataset.name) {
      playSong(item.dataset.name);
    }
  });

  filesList.addEventListener('click', (e) => {
    const item = e.target.closest('.file-item');
    if (item) {
      document.querySelectorAll('.file-item').forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    }
  });

  // ============================================================
  // 7. MENÚ CONTEXTUAL (CLIC DERECHO)
  // ============================================================

  filesList.addEventListener('contextmenu', (e) => {
    const item = e.target.closest('.file-item');
    if (!item) return;

    e.preventDefault();
    contextTargetFileName = item.dataset.name;

    document.querySelectorAll('.file-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');

    const menuWidth = 190;
    const menuHeight = 150;
    let x = e.clientX;
    let y = e.clientY;

    if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
    if (y + menuHeight > window.innerHeight) y = window.innerHeight - menuHeight - 10;

    customContextMenu.style.left = `${x}px`;
    customContextMenu.style.top = `${y}px`;
    customContextMenu.style.display = 'flex';
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#customContextMenu')) {
      customContextMenu.style.display = 'none';
    }
  });

  if (ctxPlay) {
    ctxPlay.addEventListener('click', () => {
      if (contextTargetFileName) playSong(contextTargetFileName);
      customContextMenu.style.display = 'none';
    });
  }

  if (ctxOpenLocation) {
    ctxOpenLocation.addEventListener('click', async () => {
      customContextMenu.style.display = 'none';
      if (!contextTargetFileName) return;
      const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
      try {
        await fetch(`api.php?action=open_file_location&file=${encodeURIComponent(contextTargetFileName)}&save_dir=${encodeURIComponent(saveDir)}`);
      } catch (err) {}
    });
  }

  if (ctxRename) {
    ctxRename.addEventListener('click', async () => {
      customContextMenu.style.display = 'none';
      if (!contextTargetFileName) return;

      const { value: newName } = await Swal.fire({
        title: 'Renombrar archivo',
        input: 'text',
        inputLabel: 'Nuevo nombre:',
        inputValue: contextTargetFileName,
        showCancelButton: true,
        confirmButtonText: 'Renombrar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
          if (!value || !value.trim()) return '¡El nombre no puede estar vacío!';
        }
      });

      if (newName && newName.trim() !== contextTargetFileName) {
        const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
        try {
          const form = new FormData();
          form.append('action', 'rename_file');
          form.append('old_name', contextTargetFileName);
          form.append('new_name', newName.trim());
          form.append('save_dir', saveDir);

          const res = await fetch('api.php', { method: 'POST', body: form });
          const data = await res.json();

          if (data.success) {
            Swal.fire('¡Renombrado!', `Archivo: "${data.new_name}".`, 'success');
            if (audioTitle.dataset.currentFile === contextTargetFileName) {
              audioTitle.dataset.currentFile = data.new_name;
              const fallback = parseTrackName(data.new_name);
              applyTrackDisplay(fallback.title, fallback.artist, null);
            }
            loadFilesList();
          } else {
            Swal.fire('Error', data.error || 'Ocurrió un problema.', 'error');
          }
        } catch (e) {}
      }
    });
  }

  if (ctxDelete) {
    ctxDelete.addEventListener('click', () => {
      customContextMenu.style.display = 'none';
      if (contextTargetFileName) deleteSong(contextTargetFileName);
    });
  }

  async function deleteSong(fileName) {
    const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
    const res = await Swal.fire({
      title: '¿Eliminar archivo?',
      text: `¿Eliminar "${fileName}" de tu disco duro?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#ef4444'
    });
    
    if (res.isConfirmed) {
      try {
        if (audioTitle.dataset.currentFile === fileName) {
          mainAudio.pause();
          mainAudio.removeAttribute('src');
          mainAudio.load();
          if (mainVideo) {
            mainVideo.pause();
            mainVideo.removeAttribute('src');
            mainVideo.load();
          }
          if (videoPlayerWrapper) videoPlayerWrapper.style.display = 'none';
          if (vinylRecord) vinylRecord.style.display = 'flex';
          audioScrubber.value = 0;
          currentTimeDisplay.textContent = '0:00';
          totalTimeDisplay.textContent = '0:00';
          applyTrackDisplay('Selecciona una canción', "Antonio's TuneGrab", null);
          if (vinylRecord) vinylRecord.classList.remove('spinning');
          if (sideMetaStatus) sideMetaStatus.textContent = 'Listo';
        }

        const fetchRes = await fetch(`api.php?action=delete_file&file=${encodeURIComponent(fileName)}&save_dir=${encodeURIComponent(saveDir)}`);
        const data = await fetchRes.json();
        if (data.success) {
          loadFilesList();
        } else {
          Swal.fire('Error', data.error || 'No se pudo eliminar.', 'error');
        }
      } catch (e) {}
    }
  }

  // ============================================================
  // 8. DESCARGAS Y TERMINAL EN VIVO (THROTTLED SSE)
  // ============================================================

  function updateMiniStatus(text, isBusy = false) {
    if (miniStatusText) miniStatusText.textContent = text;
    if (miniStatusChip) miniStatusChip.classList.toggle('busy', isBusy);
  }

  function logToTerminal(type, message) {
    if (message === lastLoggedMsg) return;
    lastLoggedMsg = message;

    const timeStr = new Date().toLocaleTimeString();

    if (terminalBody) {
      if (terminalBody.children.length > MAX_TERMINAL_LINES) {
        terminalBody.removeChild(terminalBody.firstChild);
      }
      const line = document.createElement('div');
      line.className = `log-line log-${type}`;
      line.textContent = `[${timeStr}] ${message}`;
      terminalBody.appendChild(line);
      terminalBody.scrollTop = terminalBody.scrollHeight;
    }
  }

  if (clearTerminalBtn) {
    clearTerminalBtn.addEventListener('click', () => {
      if (terminalBody) terminalBody.innerHTML = '';
      logToTerminal('dim', 'Consola limpiada.');
    });
  }

  function startDownloadStream(url) {
    if (currentEventSource) {
      currentEventSource.close();
    }

    setUIBusy(true);
    activeDownloadCard.style.display = 'flex';
    downloadProgressFill.style.transform = 'scaleX(0)';
    downloadPctBadge.textContent = '0%';
    downloadStatusTitle.textContent = 'Iniciando descarga...';
    downloadStatusDetail.textContent = 'Conectando con YouTube...';
    updateMiniStatus('Descargando...', true);
    if (sideMetaStatus) sideMetaStatus.textContent = 'Descargando';

    lastProgressUpdate = 0;
    currentEventSource = new EventSource(url);

    currentEventSource.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        const now = performance.now();

        if (data.type === 'progress' || data.type === 'progress_pct') {
          // Throttling: máximo 1 actualización DOM cada 150ms
          if (now - lastProgressUpdate < 150 && data.type === 'progress_pct') {
            return;
          }
          lastProgressUpdate = now;

          let pct = 0;
          if (data.type === 'progress') {
            const current = data.current || 1;
            const total = data.total || 1;
            pct = Math.round((current / total) * 100);
            downloadStatusTitle.textContent = `Descargando item ${current} de ${total}`;
            updateMiniStatus(`[${pct}%] Item ${current}/${total}`, true);
          } else {
            pct = Math.round(data.pct || 0);
            updateMiniStatus(`Descargando ${pct}%`, true);
          }

          const ratio = Math.min(1, Math.max(0, pct / 100));
          // Aceleración por hardware con scaleX (0 layout reflows)
          downloadProgressFill.style.transform = `scaleX(${ratio})`;
          downloadPctBadge.textContent = `${pct}%`;
          downloadStatusDetail.textContent = data.message;
          logToTerminal('info', data.message);
        } else if (data.type === 'complete') {
          logToTerminal('success', data.message);
          currentEventSource.close();
          currentEventSource = null;
          setUIBusy(false);
          updateMiniStatus('Listo', false);
          if (sideMetaStatus) sideMetaStatus.textContent = 'Listo';
          loadFilesList();

          downloadProgressFill.style.transform = 'scaleX(1)';
          downloadPctBadge.textContent = '100%';
          downloadStatusTitle.textContent = '¡Completado con éxito!';

          setTimeout(() => {
            activeDownloadCard.style.display = 'none';
          }, 3000);

          Swal.fire({
            title: '¡Descarga Terminada!',
            text: data.message,
            icon: 'success',
            confirmButtonText: 'Genial',
            confirmButtonColor: '#d946ef'
          });
        } else if (data.type === 'error') {
          logToTerminal('error', data.message);
          downloadStatusDetail.textContent = data.message;
        } else {
          logToTerminal(data.type || 'info', data.message);
        }
      } catch (err) {}
    };

    currentEventSource.onerror = () => {
      if (currentEventSource) {
        currentEventSource.close();
        currentEventSource = null;
      }
      setUIBusy(false);
      updateMiniStatus('Listo', false);
      if (sideMetaStatus) sideMetaStatus.textContent = 'Listo';
      loadFilesList();
      activeDownloadCard.style.display = 'none';
    };
  }

  function setUIBusy(isBusy) {
    mixBtn.disabled = isBusy;
    cleanBtn.disabled = isBusy;

    if (isBusy) {
      downloadBtn.innerHTML = '<i class="fas fa-times"></i> Cancelar Descarga';
      downloadBtn.classList.remove('btn-primary');
      downloadBtn.classList.add('btn-danger');
      downloadBtn.dataset.mode = 'cancel';
    } else {
      downloadBtn.innerHTML = '<i class="fas fa-arrow-down-to-line"></i> Iniciar Descarga';
      downloadBtn.classList.remove('btn-danger');
      downloadBtn.classList.add('btn-primary');
      downloadBtn.dataset.mode = 'download';
    }
  }

  if (downloadBtn) {
    downloadBtn.addEventListener('click', async () => {
      if (downloadBtn.dataset.mode === 'cancel') {
        try {
          await fetch('api.php?action=cancel');
          logToTerminal('error', 'Descarga cancelada por el usuario.');
          updateMiniStatus('Cancelado', false);
          if (currentEventSource) currentEventSource.close();
          setUIBusy(false);
          activeDownloadCard.style.display = 'none';
        } catch(e) {}
        return;
      }

      const url = urlInput.value.trim();
      if (!url) {
        Swal.fire({
          title: 'Falta Entrada',
          text: 'Ingresa un enlace de YouTube o término de búsqueda.',
          icon: 'warning',
          confirmButtonText: 'Entendido'
        });
        urlInput.focus();
        return;
      }

      const isUrl = url.startsWith('http://') || url.startsWith('https://') || url.includes('youtube.com') || url.includes('youtu.be');
      
      if (!isUrl) {
        executeSearch(url);
        return;
      }

      const limitType = songLimitSelect ? songLimitSelect.value : 'all';
      const customLimit = customLimitInput ? customLimitInput.value : '0';
      const format = audioFormatSelect ? audioFormatSelect.value : 'mp3';
      const mix = (mixAfterCheck && mixAfterCheck.checked) ? 1 : 0;
      const single = (singleSongCheck && singleSongCheck.checked) ? 1 : 0;
      const metadata = (metadataCheck && metadataCheck.checked) ? 1 : 0;
      const saveDir = saveDirInput ? saveDirInput.value.trim() : '';

      let limit = 'all';
      if (limitType === 'custom') {
        limit = customLimit > 0 ? customLimit : 'all';
      } else if (limitType !== 'all') {
        limit = limitType;
      }

      const urlParams = `action=download&url=${encodeURIComponent(url)}&save_dir=${encodeURIComponent(saveDir)}&limit=${limit}&format=${format}&mix=${mix}&single=${single}&metadata=${metadata}`;
      startDownloadStream(`api.php?${urlParams}`);
    });
  }

  // ============================================================
  // 9. MODAL DE BÚSQUEDA EN YOUTUBE
  // ============================================================

  if (searchModalClose && searchModalOverlay) {
    searchModalClose.addEventListener('click', () => {
      searchModalOverlay.classList.remove('active');
    });
    searchModalOverlay.addEventListener('click', (e) => {
      if (e.target === searchModalOverlay) {
        searchModalOverlay.classList.remove('active');
      }
    });
  }

  async function executeSearch(query) {
    const limitType = songLimitSelect ? songLimitSelect.value : '10';
    let limit = limitType === 'custom' && customLimitInput ? customLimitInput.value : limitType;
    if (limit === 'all') limit = 10;
    
    setUIBusy(true);
    logToTerminal('info', `Buscando "${query}" en YouTube...`);
    updateMiniStatus('Buscando...', true);

    try {
      const res = await fetch(`api.php?action=search&q=${encodeURIComponent(query)}&limit=${limit}`);
      const data = await res.json();
      
      if (data.success && data.results && data.results.length > 0) {
        logToTerminal('success', `Se encontraron ${data.results.length} resultados.`);
        renderSearchResults(data.results);
        searchModalOverlay.classList.add('active');
      } else {
        Swal.fire('Sin resultados', 'Intenta con otro término de búsqueda.', 'warning');
      }
    } catch (e) {
      logToTerminal('error', 'Error de conexión al buscar.');
    } finally {
      setUIBusy(false);
      updateMiniStatus('Listo', false);
    }
  }

  if (searchBtn) {
    searchBtn.addEventListener('click', () => {
      const q = urlInput.value.trim();
      if (q) executeSearch(q);
    });
  }

  urlInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      const val = urlInput.value.trim();
      const isUrl = val.startsWith('http://') || val.startsWith('https://') || val.includes('youtube.com') || val.includes('youtu.be');
      if (isUrl) {
        downloadBtn.click();
      } else if (val) {
        executeSearch(val);
      }
    }
  });

  let selectedSearchUrls = new Set();

  function renderSearchResults(results) {
    if (!searchResultsContainer) return;
    searchResultsContainer.innerHTML = '';
    selectedSearchUrls.clear();
    updateDownloadSelectedBtn();
    
    searchResultsContainer.innerHTML = results.map(res => `
      <div class="search-result-item" data-url="${escapeHtml(res.url)}">
        <div class="search-result-checkbox"><i class="fas fa-check"></i></div>
        <img class="search-result-thumb" src="${res.thumbnail || 'https://via.placeholder.com/90x50?text=Sin+Imagen'}" alt="Thumb" loading="lazy">
        <div class="search-result-info">
          <div class="search-result-title">${escapeHtml(res.title)}</div>
          <div class="search-result-meta">
            <span><i class="fas fa-clock"></i> ${res.duration}</span>
            <span><i class="fas fa-user"></i> ${escapeHtml(res.uploader)}</span>
          </div>
        </div>
      </div>
    `).join('');

    searchResultsContainer.querySelectorAll('.search-result-item').forEach(item => {
      item.addEventListener('click', () => {
        const u = item.dataset.url;
        if (selectedSearchUrls.has(u)) {
          selectedSearchUrls.delete(u);
          item.classList.remove('selected');
        } else {
          selectedSearchUrls.add(u);
          item.classList.add('selected');
        }
        updateDownloadSelectedBtn();
      });
    });
  }

  function updateDownloadSelectedBtn() {
    if (!downloadSelectedBtn) return;
    const count = selectedSearchUrls.size;
    downloadSelectedBtn.innerHTML = `<i class="fas fa-download"></i> Descargar Seleccionadas (${count})`;
    downloadSelectedBtn.disabled = count === 0;
  }

  if (selectAllBtn) {
    selectAllBtn.addEventListener('click', () => {
      const allItems = searchResultsContainer.querySelectorAll('.search-result-item');
      const allSelected = selectedSearchUrls.size === allItems.length && allItems.length > 0;
      
      if (allSelected) {
        allItems.forEach(item => item.classList.remove('selected'));
        selectedSearchUrls.clear();
      } else {
        allItems.forEach(item => {
          item.classList.add('selected');
          selectedSearchUrls.add(item.dataset.url);
        });
      }
      updateDownloadSelectedBtn();
    });
  }

  if (downloadSelectedBtn) {
    downloadSelectedBtn.addEventListener('click', () => {
      if (selectedSearchUrls.size === 0) return;
      const urlsArray = Array.from(selectedSearchUrls);
      urlInput.value = urlsArray.join(',');
      searchModalOverlay.classList.remove('active');

      if (singleSongCheck) singleSongCheck.checked = true;
      downloadBtn.click();
    });
  }

  // ============================================================
  // 10. HERRAMIENTAS USB / CARPETA
  // ============================================================

  if (mixBtn) {
    mixBtn.addEventListener('click', () => {
      Swal.fire({
        title: '¿Mezclar Canciones para USB?',
        text: 'Se reordenarán aleatoriamente todas las canciones para estéreos y radios.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, mezclar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
          startDownloadStream(`api.php?action=mix&save_dir=${encodeURIComponent(saveDir)}`);
        }
      });
    });
  }

  if (cleanBtn) {
    cleanBtn.addEventListener('click', () => {
      Swal.fire({
        title: '¿Limpiar Duplicados y Temporales?',
        text: 'Se eliminarán descargas incompletas (.part) y duplicados conservando la versión más reciente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
          try {
            const res = await fetch(`api.php?action=clean&save_dir=${encodeURIComponent(saveDir)}`);
            const data = await res.json();
            if (data.success) {
              Swal.fire('¡Limpieza Completada!', `Se eliminaron ${data.deleted_count} archivos (${data.freed_mb} MB).`, 'success');
              loadFilesList();
            } else {
              Swal.fire('Error', data.error || 'No se pudo limpiar.', 'error');
            }
          } catch (e) {}
        }
      });
    });
  }

  if (openFolderBtn) {
    openFolderBtn.addEventListener('click', async () => {
      try {
        const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
        await fetch(`api.php?action=open_folder&save_dir=${encodeURIComponent(saveDir)}`);
      } catch (e) {}
    });
  }

  // ============================================================
  // 11. DRAG & DROP GLOBAL
  // ============================================================

  let dragCounter = 0;

  window.addEventListener('dragenter', (e) => {
    e.preventDefault();
    dragCounter++;
    if (dragDropOverlay) dragDropOverlay.classList.add('active');
  });

  window.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dragCounter--;
    if (dragCounter <= 0 && dragDropOverlay) {
      dragCounter = 0;
      dragDropOverlay.classList.remove('active');
    }
  });

  window.addEventListener('dragover', (e) => {
    e.preventDefault();
  });

  window.addEventListener('drop', (e) => {
    e.preventDefault();
    dragCounter = 0;
    if (dragDropOverlay) dragDropOverlay.classList.remove('active');

    let text = e.dataTransfer.getData('text/plain') || e.dataTransfer.getData('text/uri-list') || '';
    text = text.trim();

    if (text) {
      urlInput.value = text;
      urlInput.focus();
    }
  });

  // ============================================================
  // 12. INICIALIZACIÓN
  // ============================================================
  initTheme();
  checkDependencies();
  loadFilesList();
});
