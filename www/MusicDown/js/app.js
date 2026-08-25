document.addEventListener('DOMContentLoaded', () => {
  // Elementos DOM
  const urlInput = document.getElementById('urlInput');
  const pasteBtn = document.getElementById('pasteBtn');
  const clearBtn = document.getElementById('clearBtn');
  
  const saveDirInput = document.getElementById('saveDirInput');
  const browseDirBtn = document.getElementById('browseDirBtn');
  
  const songLimitSelect = document.getElementById('songLimitSelect');
  const customLimitBox = document.getElementById('customLimitBox');
  const customLimitInput = document.getElementById('customLimitInput');
  
  const audioFormatSelect = document.getElementById('audioFormatSelect');
  const mixAfterCheck = document.getElementById('mixAfterCheck');
  const singleSongCheck = document.getElementById('singleSongCheck');
  const limitGroup = document.getElementById('limitGroup');
  
  const downloadBtn = document.getElementById('downloadBtn');
  const mixBtn = document.getElementById('mixBtn');
  const openFolderBtn = document.getElementById('openFolderBtn');
  const checkDepsBtn = document.getElementById('checkDepsBtn');
  
  const ytdlpBadge = document.getElementById('ytdlpBadge');
  const ffmpegBadge = document.getElementById('ffmpegBadge');
  
  const terminalBody = document.getElementById('terminalBody');
  const clearTerminalBtn = document.getElementById('clearTerminalBtn');
  
  const progressContainer = document.getElementById('progressContainer');
  const progressBarFill = document.getElementById('progressBarFill');
  const progressStatusText = document.getElementById('progressStatusText');
  const progressPercentText = document.getElementById('progressPercentText');
  
  const filesList = document.getElementById('filesList');
  const refreshFilesBtn = document.getElementById('refreshFilesBtn');

  let currentEventSource = null;

  // 1. Lógica de Límite Personalizado y Single Song
  function updateLimitUI() {
    const isCustom = songLimitSelect && songLimitSelect.value === 'custom';
    if (customLimitBox) {
      customLimitBox.style.display = isCustom ? 'block' : 'none';
    }

    // Manejar Single Song Checkbox
    if (singleSongCheck && limitGroup) {
      if (singleSongCheck.checked) {
        limitGroup.style.opacity = '0.4';
        limitGroup.style.pointerEvents = 'none';
      } else {
        limitGroup.style.opacity = '1';
        limitGroup.style.pointerEvents = 'auto';
      }
    }
  }

  if (songLimitSelect) songLimitSelect.addEventListener('change', updateLimitUI);
  if (singleSongCheck) singleSongCheck.addEventListener('change', updateLimitUI);
  updateLimitUI();

  // Botón pegar del portapapeles
  if (pasteBtn) {
    pasteBtn.addEventListener('click', async () => {
      try {
        const text = await navigator.clipboard.readText();
        if (text) {
          urlInput.value = text.trim();
          logToTerminal('info', 'URL pegada desde el portapapeles.');
        }
      } catch (err) {
        logToTerminal('warning', 'No se pudo acceder al portapapeles automáticamente. Pega usando Ctrl+V.');
      }
    });
  }

  // Botón limpiar URL
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      urlInput.value = '';
      urlInput.focus();
    });
  }

  // Manejo de Selector de Directorio Nativo
  if (browseDirBtn) {
    browseDirBtn.addEventListener('click', async () => {
      const originalText = browseDirBtn.innerHTML;
      browseDirBtn.disabled = true;
      browseDirBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Abriendo...';
      try {
        const res = await fetch('api.php?action=pick_folder');
        const data = await res.json();
        if (data.path && data.path.trim() !== '') {
          saveDirInput.value = data.path;
          loadFilesList(); // Actualizar lista si cambió la ruta
        }
      } catch (err) {
        logToTerminal('error', 'Error al abrir el selector de carpetas: ' + err.message);
      } finally {
        browseDirBtn.disabled = false;
        browseDirBtn.innerHTML = originalText;
      }
    });
  }

  // 2. Verificar estado de dependencias (yt-dlp / ffmpeg)
  async function checkStatus() {
    try {
      const res = await fetch('api.php?action=status');
      const data = await res.json();
      
      if (data.ytdlp) {
        ytdlpBadge.className = 'badge ok';
        ytdlpBadge.querySelector('.badge-text').textContent = 'yt-dlp: Listo';
      } else {
        ytdlpBadge.className = 'badge warn';
        ytdlpBadge.querySelector('.badge-text').textContent = 'yt-dlp: Falta/Incompleto';
      }

      if (data.ffmpeg) {
        ffmpegBadge.className = 'badge ok';
        ffmpegBadge.querySelector('.badge-text').textContent = 'FFmpeg: Listo';
      } else {
        ffmpegBadge.className = 'badge warn';
        ffmpegBadge.querySelector('.badge-text').textContent = 'FFmpeg: No Instalado';
      }
    } catch (e) {
      console.error('Error al comprobar dependencias', e);
    }
  }

  // 3. Escribir líneas en el Terminal
  function logToTerminal(type, message) {
    const line = document.createElement('div');
    line.className = `log-line log-${type}`;
    line.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
    terminalBody.appendChild(line);
    terminalBody.scrollTop = terminalBody.scrollHeight;
  }

  if (clearTerminalBtn) {
    clearTerminalBtn.addEventListener('click', () => {
      terminalBody.innerHTML = '';
      logToTerminal('dim', 'Consola limpiada.');
    });
  }

  // 4. Iniciar Descarga con SSE (Server-Sent Events)
  function startStreamTask(url) {
    if (currentEventSource) {
      currentEventSource.close();
    }

    setUIBusy(true);
    progressContainer.classList.add('active');
    progressBarFill.style.width = '0%';
    progressPercentText.textContent = '0%';
    progressStatusText.textContent = 'Iniciando conexión...';

    currentEventSource = new EventSource(url);

    currentEventSource.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);

        if (data.type === 'progress') {
          const current = data.current || 0;
          const total = data.total || 1;
          const pct = Math.round((current / total) * 100);
          progressBarFill.style.width = `${pct}%`;
          progressPercentText.textContent = `${pct}%`;
          progressStatusText.textContent = `Descargando canción ${current} de ${total}`;
          logToTerminal('info', data.message);
        } else if (data.type === 'progress_pct') {
          const pct = Math.round(data.pct || 0);
          progressBarFill.style.width = `${pct}%`;
          progressPercentText.textContent = `${pct}%`;
          if (data.current && data.total) {
            progressStatusText.textContent = `Descargando canción ${data.current} de ${data.total} (${pct}%)`;
          } else {
            progressStatusText.textContent = `Procesando descarga (${pct}%)`;
          }
        } else if (data.type === 'complete') {
          logToTerminal('success', data.message);
          currentEventSource.close();
          currentEventSource = null;
          setUIBusy(false);
          checkStatus();
          loadFilesList();
          progressBarFill.style.width = '100%';
          progressPercentText.textContent = '100%';
          progressStatusText.textContent = '¡Completado!';
          Swal.fire({
            title: '¡Proceso Terminado!',
            text: data.message,
            icon: 'success',
            confirmButtonText: 'Genial',
            confirmButtonColor: '#d6a3ff',
            background: '#ffffff',
            color: '#6e5d7e',
            backdrop: `rgba(240, 220, 255, 0.4)`
          });
        } else if (data.type === 'error') {
          logToTerminal('error', data.message);
          if (data.message.toLowerCase().includes('falta')) {
              Swal.fire({
                title: 'Faltan Motores',
                text: data.message,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#ff9999'
              });
          }
        } else {
          logToTerminal(data.type || 'info', data.message);
        }
      } catch (err) {
        logToTerminal('dim', event.data);
      }
    };

    currentEventSource.onerror = (err) => {
      console.error('Error de conexión SSE:', err);
      logToTerminal('error', 'La conexión con el servidor finalizó o se interrumpió.');
      if (currentEventSource) {
        currentEventSource.close();
        currentEventSource = null;
      }
      setUIBusy(false);
      checkStatus();
      loadFilesList();
      Swal.fire({
        title: 'Error de Conexión',
        text: 'La conexión con el servidor finalizó o se interrumpió.',
        icon: 'error',
        confirmButtonText: 'Entendido'
      });
    };
  }

  function setUIBusy(isBusy) {
    downloadBtn.disabled = isBusy;
    mixBtn.disabled = isBusy;
    checkDepsBtn.disabled = isBusy;
    
    if (isBusy) {
      downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Descargando...';
    } else {
      downloadBtn.innerHTML = '<i class="fas fa-download"></i> Iniciar Descarga';
    }
  }

  // Evento Clic en Iniciar Descarga
  downloadBtn.addEventListener('click', () => {
    const url = urlInput.value.trim();
    if (!url) {
      alert('Por favor ingresa una URL válida de YouTube o Playlist.');
      urlInput.focus();
      return;
    }

    const limitType = songLimitSelect ? songLimitSelect.value : 'all';
    const customLimit = customLimitInput ? customLimitInput.value : '0';
    const format = audioFormatSelect ? audioFormatSelect.value : 'mp3';
    const mix = (mixAfterCheck && mixAfterCheck.checked) ? 1 : 0;
    const single = (singleSongCheck && singleSongCheck.checked) ? 1 : 0;
    const saveDir = saveDirInput ? saveDirInput.value.trim() : '';

    let limit = 'all';
    if (limitType === 'custom') {
        limit = customLimit > 0 ? customLimit : 'all';
    } else if (limitType !== 'all') {
        limit = limitType;
    }

    setUIBusy(true);
    clearTerminalBtn.click();
    
    const urlParams = `action=download&url=${encodeURIComponent(url)}&save_dir=${encodeURIComponent(saveDir)}&limit=${limit}&format=${format}&mix=${mix}&single=${single}`;
    startStreamTask(`api.php?${urlParams}`);
  });

  // Botón Mezclar Canciones
  mixBtn.addEventListener('click', () => {
    if (confirm('¿Deseas mezclar aleatoriamente todas las canciones descargadas ahora?')) {
      logToTerminal('info', 'Ejecutando script de mezcla aleatoria...');
      const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
      startStreamTask(`api.php?action=mix&save_dir=${encodeURIComponent(saveDir)}`);
    }
  });

  // Botón Verificar/Instalar Dependencias
  checkDepsBtn.addEventListener('click', async () => {
    logToTerminal('info', 'Abriendo ventana de instalación de dependencias...');
    try {
      await fetch('api.php?action=install_deps');
      logToTerminal('warning', 'Se ha abierto una consola externa. Espera a que termine e indique completado, luego presiona F5 para refrescar.');
    } catch (e) {
      logToTerminal('error', 'Error al abrir el instalador.');
    }
  });

  // Botón Abrir Carpeta
  openFolderBtn.addEventListener('click', async () => {
    try {
      const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
      await fetch(`api.php?action=open_folder&save_dir=${encodeURIComponent(saveDir)}`);
      logToTerminal('info', 'Carpeta de descargas abierta en el Explorador de Windows.');
    } catch (e) {
      logToTerminal('error', 'No se pudo abrir la carpeta.');
    }
  });

  // 5. Cargar lista de archivos de audio descargados
  async function loadFilesList() {
    try {
      const saveDir = saveDirInput ? saveDirInput.value.trim() : '';
      const res = await fetch(`api.php?action=list_files&save_dir=${encodeURIComponent(saveDir)}`);
      const data = await res.json();
      
      if (!data.files || data.files.length === 0) {
        filesList.innerHTML = '<div class="empty-state">No hay canciones descargadas aún en la carpeta.</div>';
        return;
      }

      filesList.innerHTML = data.files.map(file => `
        <div class="file-item">
          <div class="file-info">
            <i class="fas ${file.ext === 'mp4' ? 'fa-video' : 'fa-music'} file-icon"></i>
            <span class="file-name" title="${file.name}">${file.name}</span>
          </div>
          <span class="file-meta">${file.size}</span>
        </div>
      `).join('');
    } catch (e) {
      console.error('Error al cargar lista de archivos', e);
    }
  }

  if (refreshFilesBtn) {
    refreshFilesBtn.addEventListener('click', loadFilesList);
  }

  // Interceptar clicks en enlaces del footer para abrirlos en el navegador predeterminado del sistema
  document.querySelectorAll('footer a').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      fetch(`api.php?action=open_url&url=${encodeURIComponent(link.href)}`);
    });
  });

  // Inicialización al cargar la página
  checkStatus();
  loadFilesList();
});
