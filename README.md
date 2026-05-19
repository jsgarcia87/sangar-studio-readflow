# Sangar Studio ReadFlow - Visual Reading Time & AI Audio 🎙️

[![WordPress Version](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg?style=flat-square&logo=wordpress)](https://wordpress.org)
[![OpenAI TTS](https://img.shields.io/badge/AI--Voice-OpenAI%20TTS-indigo.svg?style=flat-square&logo=openai)](https://platform.openai.com)
[![License](https://img.shields.io/badge/License-GPL%202.0-orange.svg?style=flat-square)](https://gnu.org/licenses/gpl-2.0.html)
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/X8X41Z55AM)

**Sangar Studio ReadFlow** es un plugin premium y de alta fidelidad para WordPress que revoluciona la experiencia de lectura y consumo auditivo en tu blog. Calcula automáticamente el tiempo estimado de lectura de tus entradas y despliega un widget con un **reproductor de audio personalizado y glassmórfico** de última generación.

Gracias a la integración con la API de **OpenAI Text-to-Speech (TTS)**, **Sangar Studio ReadFlow** sintetiza de manera fluida y ultra-realista la voz de tus artículos. Si el administrador no desea usar Inteligencia Artificial, el plugin conmuta de manera inteligente y sin configuraciones extra al sintetizador nativo del navegador del usuario (**Web Speech API Fallback**).

---

## ✨ Características Destacadas

*   **⏱️ Indicador de Tiempo Exacto**: Calcula con precisión los minutos de lectura del post basándose en un parámetro regulable de Palabras por Minuto (PPM).
*   **🎨 Widget Glassmorphism Ultra Premium**: Un reproductor translúcido con desenfoque dinámico, micro-animación de ecualizador de ondas de sonido en vivo al reproducir, e iconos SVG limpios y vectoriales.
*   **🤖 Voces OpenAI Profesionales**: Acceso a seis espectaculares modelos de voz humana (Alloy, Echo, Fable, Onyx, Nova, Shimmer) con velocidades regulables.
*   **💾 Caché Local Inteligente**: El plugin compila y guarda las pistas generadas en disco (`wp-content/uploads/sangar-studio-readflow/`) en formato MP3 de manera automática. Esto reduce el tiempo de espera a **0 ms** para el resto de lectores y previene cargos repetitivos en tu cuenta de OpenAI.
*   **🔗 División de Texto Sin Límites (Chunking)**: Esquiva la restricción de 4,096 caracteres de OpenAI dividiendo artículos extensos en bloques semánticos y fusionándolos secuencialmente a nivel de archivos MP3 de manera transparente.
*   **⚡ Pre-generación Automática**: Compila el audio MP3 en segundo plano inmediatamente cuando haces clic en "Publicar", evitando esperas para tu primer lector.
*   **🔧 Test de Conexión en Admin**: Un panel interactivo tipo SaaS con un simulador donde puedes probar tu API Key y escuchar locuciones al instante sin escribir en disco.
*   **📥 Descarga de MP3**: Brinda la opción a tus lectores de descargar los archivos de audio locales para escucharlos offline.
*   **🌐 Fallback Universal**: Resiliencia integrada que activa de manera silenciosa la síntesis nativa del navegador ante fallas de cuota o caídas de red de la API de OpenAI.

---

## 📂 Arquitectura del Plugin

El código sigue estándares estrictos de la comunidad de WordPress (WordPress Coding Standards) y utiliza una arquitectura orientada a objetos (POO) limpia y escalable:

*   `sangar-studio-readflow.php`: Inicializador del ciclo de vida, constantes globales e inicialización modular del plugin.
*   `uninstall.php`: Limpieza total y segura de la base de datos (tanto opciones legacy como activas) y remoción recursiva del directorio de audios en la desinstalación.
*   `includes/class-sangar-studio-readflow-settings.php`: Registro de opciones sanitizadas, permisos del administrador y controladores AJAX de mantenimiento y validación enriquecidos con `wp_unslash()` y `wp_kses()`.
*   `includes/class-sangar-studio-readflow-tts.php`: Lógica de fragmentación de párrafos, llamadas HTTP robustas mediante el núcleo de WP, fusión binaria y gestión de eventos de invalidación de caché (`save_post`).
*   `includes/class-sangar-studio-readflow-frontend.php`: Renderizado del componente widget, inyección dinámica de propiedades personalizadas CSS basadas en tus colores de acento preferidos y soporte de shortcodes.
*   `assets/`: Compendio de hojas de estilo (`css/`) y controladores de lógica interactiva (`js/`) tanto para el entorno administrativo como de cara al usuario final.

---

## 🚀 Inserción del Widget

El widget se inserta automáticamente en las posiciones configuradas en el panel administrativo. Si prefieres colocarlo manualmente, cuentas con soporte para shortcodes:

> [!TIP]
> Puedes incrustar el reproductor en cualquier sección del editor clásico o de Gutenberg utilizando el shortcode oficial: `[sangar-studio-readflow]`
> *(El shortcode heredado `[readio]` también es completamente soportado por motivos de compatibilidad hacia atrás).*
> 
> O bien llamándolo directamente en las plantillas PHP de tu tema:
> `<?php echo do_shortcode('[sangar-studio-readflow]'); ?>`

---

## 🛠️ Seguridad Aplicada

Para garantizar que el plugin sea considerado apto para entornos de alta seguridad y producción:
1.  **Anti-acceso directo**: Cada archivo PHP bloquea solicitudes no iniciadas por el núcleo mediante `if ( ! defined( 'ABSPATH' ) ) exit;`.
2.  **Tokens Nonces**: Validaciones CSRF exhaustivas en cada callback AJAX.
3.  **Sanitización y Escape Total**: Uso de `wp_unslash()`, `sanitize_text_field`, `sanitize_hex_color`, `absint`, `esc_html`, `esc_attr`, `esc_url` y `wp_kses()` en la entrada y salida de datos, anulando vulnerabilidades XSS.
4.  **wp_remote_post()**: Peticiones a red seguras para la intercomunicación del servidor, garantizando compatibilidad con proxies e infraestructuras hosting restrictivas.

---

## 💙 Realizado por
Este maravilloso plugin ha sido meticulosamente programado y diseñado por **Sangar Studio**.

Si te ha sido de utilidad o deseas apoyar el desarrollo de más herramientas de código abierto como esta, ¡invítanos a un café!

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/X8X41Z55AM)

---
*Desarrollado bajo licencia GPLv2 o posterior para garantizar la libertad de uso, modificación y distribución.*
