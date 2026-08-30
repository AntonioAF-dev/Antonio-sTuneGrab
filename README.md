<div align="center">

# 🎵 Antonio's TuneGrab 🎵
**Descargador de Playlists y Música de YouTube de Alta Calidad**

[![Licencia](https://img.shields.io/badge/Licencia-Open_Source-green.svg)](#licencia)
[![Plataforma](https://img.shields.io/badge/Plataforma-Windows-blue.svg)](#)
[![Desarrollador](https://img.shields.io/badge/Desarrollador-AntonioAF--dev-purple.svg)](https://github.com/AntonioAF-dev)

### 📥 [Descargar Última Versión (v1.0.1)](https://github.com/AntonioAF-dev/Antonio-sTuneGrab/releases/tag/v1.0.1)

</div>

---

## 📖 Acerca del Proyecto

**Antonio's TuneGrab** es una potente aplicación de escritorio para Windows diseñada para descargar música y videos desde YouTube y otras plataformas soportadas. 

Proporciona una interfaz gráfica moderna, intuitiva y profesional para herramientas de línea de comandos estándar de la industria como `yt-dlp` y `FFmpeg`, evitando que el usuario tenga que interactuar con terminales complejas. Gracias a la tecnología de **PHP Desktop**, la aplicación web funciona de forma nativa en tu computadora sin depender de servidores o navegadores externos.

---

## ✨ Características Principales

*   **🎧 Múltiples Formatos de Audio:** Descarga en MP3, M4A, FLAC o WAV según tus necesidades (compresión vs calidad sin pérdida).
*   **🎬 Soporte para Video:** Opción de descarga en MP4 (Audio + Video), ideal para dispositivos estéreo de autos, CarPlay, o simplemente para guardar videoclips.
*   **📋 Descarga de Playlists:** Pega el enlace de una playlist y configura un límite de descarga (por ejemplo, descargar solo las primeras 10 canciones o toda la playlist completa).
*   **🎛️ Mezclador Integrado:** Opción para mezclar las canciones automáticamente al finalizar las descargas, ideal para crear compilados aleatorios.
*   **💻 Terminal en Vivo (SSE):** Observa el progreso real de las descargas en la terminal integrada dentro de la misma interfaz. No más ventanas de CMD flotando por la pantalla.
*   **📂 Biblioteca Local:** Visualiza y reproduce rápidamente los archivos descargados directamente en la aplicación.
*   **🎨 Interfaz Responsiva y Moderna:** Un diseño estéticamente agradable con tipografía escalable que se adapta tanto a pantallas pequeñas como a monitores ultra-anchos.

---

## 🛠️ Tecnologías y Dependencias

Para lograr este nivel de integración, el proyecto utiliza potentes motores bajo el capó:

1.  [**PHP Desktop (Chrome)**](https://github.com/cztomczak/phpdesktop): Permite envolver aplicaciones web (PHP/HTML/JS) como aplicaciones de escritorio nativas en Windows.
2.  [**yt-dlp**](https://github.com/yt-dlp/yt-dlp): Un fork avanzado de youtube-dl para extraer el audio y video de las plataformas.
3.  [**FFmpeg**](https://ffmpeg.org/): Utilizado para la conversión de formatos multimedia (unión de audio y video, extracción y compresión a MP3/FLAC).

La interfaz fue construida con HTML5, CSS Nativo (con diseño fluido y responsivo) y JavaScript puro (Vanilla JS).

---

## 🚀 Guía de Instalación y Uso

Dado que el programa utiliza binarios compilados portables, la instalación es muy sencilla.

### Prerrequisitos
Asegúrate de que los ejecutables `yt-dlp.exe` y `ffmpeg.exe` (junto con `ffprobe.exe`) estén presentes en la carpeta `www/MusicDown/` de este proyecto.

### Cómo ejecutarlo
1. [**Descarga el archivo ZIP desde la sección de Releases**](https://github.com/AntonioAF-dev/Antonio-sTuneGrab/releases/tag/v1.0.1) y extráelo en tu computadora.
2. Simplemente haz doble clic en el archivo `Antonio's TuneGrab.exe` para abrir la aplicación.
3. ¡La interfaz gráfica se abrirá de inmediato! No aparecerán consolas de depuración molestas de fondo.

### Uso Básico
1. Copia un enlace de un video o playlist de YouTube y haz clic en **Pegar**.
2. Selecciona tu carpeta de descargas haciendo clic en **Examinar...**.
3. Elige el formato que necesites (Ej: MP3 para máxima compatibilidad o MP4 si necesitas video).
4. Haz clic en **Iniciar Descarga** y observa la magia suceder en la terminal en vivo.

---

## ⚖️ Licencia y Atribución

Este software se rige por las reglas de atribución y licencias de código abierto de sus dependencias:

*   **yt-dlp** se distribuye bajo la licencia **Unlicense / GPLv3+**.
*   **FFmpeg** se rige por la **GPL / LGPL**.
*   **PHP Desktop** utiliza la licencia **BSD 3-Clause**.

El código de **Antonio's TuneGrab** actúa exclusivamente como una interfaz gráfica (*wrapper*) de agregación que se comunica con estas herramientas externas a través de línea de comandos, respetando la filosofía y compatibilidad de las licencias originales. Los ejecutables de terceros se utilizan en su forma original sin alteraciones.

---
<div align="center">
Integración y desarrollo de interfaz por <b><a href="https://github.com/AntonioAF-dev">AntonioAF-dev</a></b>.
</div>
