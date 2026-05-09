<?php
/**
 * Readio Settings Page Class
 * Handles plugin configuration and administration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Readio_Settings {

    public function __construct() {
        // Menu creation
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        
        // Register options
        add_action( 'admin_init', [ $this, 'register_plugin_settings' ] );
        
        // Enqueue styles/scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        
        // AJAX: Clear audio cache
        add_action( 'wp_ajax_readio_clear_cache', [ $this, 'ajax_clear_cache' ] );
        
        // AJAX: Test API Connection
        add_action( 'wp_ajax_readio_test_api', [ $this, 'ajax_test_api' ] );
    }

    /**
     * Add settings page under Settings menu.
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'Readio Settings', 'readio' ),
            'Readio 🎙️',
            'manage_options',
            'readio-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings in WordPress.
     */
    public function register_plugin_settings() {
        $args = [ 'sanitize_callback' => 'sanitize_text_field' ];
        
        register_setting( 'readio_settings_group', 'readio_api_key', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_api_key' ],
            'default'           => '',
        ]);
        
        register_setting( 'readio_settings_group', 'readio_voice', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'alloy',
        ]);

        register_setting( 'readio_settings_group', 'readio_model', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'tts-1',
        ]);

        register_setting( 'readio_settings_group', 'readio_wpm', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 200,
        ]);

        register_setting( 'readio_settings_group', 'readio_position', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'before',
        ]);

        register_setting( 'readio_settings_group', 'readio_accent_color', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#6366f1',
        ]);

        register_setting( 'readio_settings_group', 'readio_enable_ai', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ]);

        register_setting( 'readio_settings_group', 'readio_auto_generate', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ]);

        register_setting( 'readio_settings_group', 'readio_show_download', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ]);
    }

    /**
     * Sanitize API Key securely.
     */
    public function sanitize_api_key( $value ) {
        $value = sanitize_text_field( trim( $value ) );
        return $value;
    }

    /**
     * Enqueue asset files in Admin dashboard.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_readio-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'readio-admin-style', READIO_URL . 'assets/css/admin.css', [], READIO_VERSION );
        
        wp_enqueue_script( 'readio-admin-script', READIO_URL . 'assets/js/admin.js', [ 'jquery', 'wp-color-picker' ], READIO_VERSION, true );
        
        wp_localize_script( 'readio-admin-script', 'readio_admin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'readio_admin_nonce' ),
            'loading_text' => __( 'Procesando...', 'readio' ),
            'test_success' => __( '¡Conexión exitosa! Reproduciendo audio de prueba...', 'readio' ),
            'test_fail' => __( 'Error en la conexión. Revisa tu API Key y saldo.', 'readio' )
        ]);
    }

    /**
     * Render the custom, high-fidelity setting dashboard page.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get saved settings or defaults
        $api_key        = get_option( 'readio_api_key', '' );
        $voice          = get_option( 'readio_voice', 'alloy' );
        $model          = get_option( 'readio_model', 'tts-1' );
        $wpm            = get_option( 'readio_wpm', 200 );
        $position       = get_option( 'readio_position', 'before' );
        $accent_color   = get_option( 'readio_accent_color', '#6366f1' );
        $enable_ai      = get_option( 'readio_enable_ai', true );
        $auto_generate  = get_option( 'readio_auto_generate', false );
        $show_download  = get_option( 'readio_show_download', true );

        // Mask API Key for display if set
        $masked_key = '';
        if ( ! empty( $api_key ) ) {
            $masked_key = substr( $api_key, 0, 7 ) . '...' . substr( $api_key, -4 );
        }
        ?>
        <div class="readio-admin-wrap">
            <header class="readio-header">
                <div class="readio-logo-area">
                    <span class="readio-icon">🎙️</span>
                    <div class="readio-title-block">
                        <h1>Readio</h1>
                        <p class="readio-tagline"><?php esc_html_e( 'Visual Reading Time & Premium AI Voice Generator', 'readio' ); ?></p>
                    </div>
                </div>
                <div class="readio-version-tag">
                    v<?php echo esc_html( READIO_VERSION ); ?>
                </div>
            </header>

            <div class="readio-container">
                <main class="readio-main-content">
                    <form method="post" action="options.php" class="readio-settings-form">
                        <?php settings_fields( 'readio_settings_group' ); ?>
                        
                        <!-- TAB CARD 1: API SETTINGS -->
                        <div class="readio-card">
                            <div class="readio-card-header">
                                <span class="card-icon">🔑</span>
                                <h2><?php esc_html_e( 'Configuración de Voz por Inteligencia Artificial', 'readio' ); ?></h2>
                            </div>
                            <div class="readio-card-body">
                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_enable_ai">
                                        <?php esc_html_e( 'Activar Voz por IA (OpenAI)', 'readio' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <label class="readio-switch">
                                            <input type="checkbox" name="readio_enable_ai" id="readio_enable_ai" value="1" <?php checked( $enable_ai, true ); ?>>
                                            <span class="readio-slider"></span>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Si está desactivado, el plugin usará automáticamente la voz nativa del navegador (gratuita) sin realizar llamadas API.', 'readio' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-conditional-ai-fields" style="<?php echo $enable_ai ? '' : 'display:none;'; ?>">
                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_api_key_input">
                                            <?php esc_html_e( 'OpenAI API Key', 'readio' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <div class="readio-api-input-container">
                                                <input type="password" 
                                                       name="readio_api_key" 
                                                       id="readio_api_key_input" 
                                                       value="<?php echo esc_attr( $api_key ); ?>" 
                                                       placeholder="<?php echo ! empty( $masked_key ) ? 'sk-••••••••••••••••••••••••' : 'sk-...'; ?>"
                                                       class="regular-text readio-input" />
                                                <button type="button" class="readio-toggle-password" id="readio-toggle-pw-btn">👁️</button>
                                            </div>
                                            <p class="description"><?php echo sprintf( __( 'Inserta tu API key de OpenAI. Puedes obtener una en tu <a href="%s" target="_blank">Consola de OpenAI</a>.', 'readio' ), 'https://platform.openai.com/api-keys' ); ?></p>
                                        </div>
                                    </div>

                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_voice">
                                            <?php esc_html_e( 'Voz predeterminada de IA', 'readio' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <select name="readio_voice" id="readio_voice" class="readio-select">
                                                <option value="alloy" <?php selected( $voice, 'alloy' ); ?>>Alloy (<?php esc_html_e( 'Voz balanceada / Neutra', 'readio' ); ?>)</option>
                                                <option value="echo" <?php selected( $voice, 'echo' ); ?>>Echo (<?php esc_html_e( 'Voz cálida / Masculina', 'readio' ); ?>)</option>
                                                <option value="fable" <?php selected( $voice, 'fable' ); ?>>Fable (<?php esc_html_e( 'Voz expresiva / Dramática', 'readio' ); ?>)</option>
                                                <option value="onyx" <?php selected( $voice, 'onyx' ); ?>>Onyx (<?php esc_html_e( 'Voz profunda / Masculina', 'readio' ); ?>)</option>
                                                <option value="nova" <?php selected( $voice, 'nova' ); ?>>Nova (<?php esc_html_e( 'Voz enérgica / Femenina', 'readio' ); ?>)</option>
                                                <option value="shimmer" <?php selected( $voice, 'shimmer' ); ?>>Shimmer (<?php esc_html_e( 'Voz clara / Femenina', 'readio' ); ?>)</option>
                                            </select>
                                            <p class="description"><?php esc_html_e( 'Selecciona la voz que más encaje con el tono de tu contenido. Todas están optimizadas para múltiples idiomas.', 'readio' ); ?></p>
                                        </div>
                                    </div>

                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_model">
                                            <?php esc_html_e( 'Modelo TTS de OpenAI', 'readio' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <select name="readio_model" id="readio_model" class="readio-select">
                                                <option value="tts-1" <?php selected( $model, 'tts-1' ); ?>>tts-1 (<?php esc_html_e( 'Rápido y de baja latencia - Recomendado', 'readio' ); ?>)</option>
                                                <option value="tts-1-hd" <?php selected( $model, 'tts-1-hd' ); ?>>tts-1-hd (<?php esc_html_e( 'Alta definición - Calidad superior pero mayor coste', 'readio' ); ?>)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_auto_generate">
                                            <?php esc_html_e( 'Pre-generar audio', 'readio' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <label class="readio-switch">
                                                <input type="checkbox" name="readio_auto_generate" id="readio_auto_generate" value="1" <?php checked( $auto_generate, true ); ?>>
                                                <span class="readio-slider"></span>
                                            </label>
                                            <p class="description"><?php esc_html_e( 'Generar automáticamente el archivo MP3 del post al guardarlo o publicarlo. Esto evita tiempos de espera para el primer lector de la entrada.', 'readio' ); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB CARD 2: FRONTEND SETTINGS -->
                        <div class="readio-card">
                            <div class="readio-card-header">
                                <span class="card-icon">🎨</span>
                                <h2><?php esc_html_e( 'Personalización del Widget Visual', 'readio' ); ?></h2>
                            </div>
                            <div class="readio-card-body">
                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_wpm">
                                        <?php esc_html_e( 'Velocidad de Lectura (Palabras por minuto)', 'readio' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <div class="readio-range-container">
                                            <input type="range" name="readio_wpm" id="readio_wpm" min="100" max="400" step="10" value="<?php echo esc_attr( $wpm ); ?>" class="readio-range">
                                            <span id="readio_wpm_val" class="readio-badge"><?php echo esc_html( $wpm ); ?> PPM</span>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'El promedio de lectura humana es de 200 PPM para textos estándar y 250 PPM para lectura rápida.', 'readio' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_position">
                                        <?php esc_html_e( 'Ubicación en el Post', 'readio' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="readio_position" id="readio_position" class="readio-select">
                                            <option value="before" <?php selected( $position, 'before' ); ?>><?php esc_html_e( 'Antes del contenido (Recomendado)', 'readio' ); ?></option>
                                            <option value="after" <?php selected( $position, 'after' ); ?>><?php esc_html_e( 'Después del contenido', 'readio' ); ?></option>
                                            <option value="both" <?php selected( $position, 'both' ); ?>><?php esc_html_e( 'Antes y después del contenido', 'readio' ); ?></option>
                                            <option value="manual" <?php selected( $position, 'manual' ); ?>><?php esc_html_e( 'Insertar manualmente vía Shortcode o PHP', 'readio' ); ?></option>
                                        </select>
                                        <p class="description"><?php echo sprintf( __( 'Si eliges "Insertar manualmente", puedes pegar el shortcode <code>[readio]</code> o llamar a <code>echo do_shortcode(\'[readio]\');</code> en tus plantillas.', 'readio' ), '' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_accent_color">
                                        <?php esc_html_e( 'Color de acento', 'readio' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <input type="text" name="readio_accent_color" id="readio_accent_color" value="<?php echo esc_attr( $accent_color ); ?>" class="readio-color-field" data-default-color="#6366f1">
                                        <p class="description"><?php esc_html_e( 'Define el color de los botones del reproductor, barras de progreso y elementos interactivos para integrarse con tu tema.', 'readio' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_show_download">
                                        <?php esc_html_e( 'Permitir descarga de Audio', 'readio' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <label class="readio-switch">
                                            <input type="checkbox" name="readio_show_download" id="readio_show_download" value="1" <?php checked( $show_download, true ); ?>>
                                            <span class="readio-slider"></span>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Muestra un enlace discreto de descarga en el reproductor personalizado (solo disponible para voz IA).', 'readio' ); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT ACTIONS -->
                        <div class="readio-submit-area">
                            <?php submit_button( __( 'Guardar Configuración', 'readio' ), 'primary readio-btn-save' ); ?>
                        </div>
                    </form>
                </main>

                <!-- ASIDE: API TESTER & UTILITIES -->
                <aside class="readio-sidebar">
                    <!-- CARD 3: TESTER -->
                    <div class="readio-card readio-tester-card" id="readio-tester-box" style="<?php echo $enable_ai ? '' : 'display:none;'; ?>">
                        <div class="readio-card-header">
                            <span class="card-icon">⚡</span>
                            <h2><?php esc_html_e( 'Probar Conexión OpenAI', 'readio' ); ?></h2>
                        </div>
                        <div class="readio-card-body">
                            <p class="tester-intro"><?php esc_html_e( 'Genera un audio de prueba al instante para verificar tus credenciales y escuchar el resultado.', 'readio' ); ?></p>
                            <div class="readio-tester-form">
                                <textarea id="readio-test-text" class="readio-textarea" placeholder="<?php esc_html_e( 'Escribe unas pocas palabras aquí...', 'readio' ); ?>">¡Hola! Readio está configurado correctamente y listo para dar voz a tus posts.</textarea>
                                <button type="button" id="readio-btn-test-api" class="button readio-btn-accent">
                                    <span>🎙️ <?php esc_html_e( 'Probar ahora', 'readio' ); ?></span>
                                </button>
                                <div id="readio-tester-status" class="readio-tester-status"></div>
                                <div id="readio-tester-audio-container" style="display: none; margin-top: 15px;">
                                    <audio id="readio-tester-audio" controls style="width: 100%;"></audio>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 4: CACHE MANAGER -->
                    <div class="readio-card readio-danger-card">
                        <div class="readio-card-header">
                            <span class="card-icon">🧹</span>
                            <h2><?php esc_html_e( 'Mantenimiento del Plugin', 'readio' ); ?></h2>
                        </div>
                        <div class="readio-card-body">
                            <p><?php esc_html_e( 'Los archivos de audio de IA generados se guardan localmente para no repetir peticiones costosas a OpenAI.', 'readio' ); ?></p>
                            <div class="readio-stats-row">
                                <span class="stats-label"><?php esc_html_e( 'Audios en Caché:', 'readio' ); ?></span>
                                <strong id="readio-cache-count"><?php echo esc_html( self::get_cached_audios_count() ); ?></strong>
                            </div>
                            <div class="readio-stats-row">
                                <span class="stats-label"><?php esc_html_e( 'Espacio Ocupado:', 'readio' ); ?></span>
                                <strong id="readio-cache-size"><?php echo esc_html( self::get_cached_audios_size() ); ?></strong>
                            </div>
                            <button type="button" id="readio-btn-clear-cache" class="button readio-btn-danger">
                                🗑️ <?php esc_html_e( 'Limpiar Todos los Audios', 'readio' ); ?>
                            </button>
                            <p class="description danger-description"><?php esc_html_e( 'Atención: Al borrar el caché se eliminarán todos los archivos MP3 locales. Los audios se volverán a generar la próxima vez que los usuarios los soliciten.', 'readio' ); ?></p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
        <?php
    }

    /**
     * Get the count of locally cached audio MP3 files.
     */
    public static function get_cached_audios_count() {
        $upload_dir = wp_upload_dir();
        $readio_dir = $upload_dir['basedir'] . '/readio';
        if ( ! file_exists( $readio_dir ) ) {
            return 0;
        }

        $files = glob( $readio_dir . '/*.mp3' );
        return count( $files );
    }

    /**
     * Get total size of locally cached audio MP3 files.
     */
    public static function get_cached_audios_size() {
        $upload_dir = wp_upload_dir();
        $readio_dir = $upload_dir['basedir'] . '/readio';
        if ( ! file_exists( $readio_dir ) ) {
            return '0 B';
        }

        $files = glob( $readio_dir . '/*.mp3' );
        $total_bytes = 0;
        foreach ( $files as $file ) {
            if ( is_file( $file ) ) {
                $total_bytes += filesize( $file );
            }
        }

        if ( $total_bytes === 0 ) {
            return '0 B';
        }

        $units = [ 'B', 'KB', 'MB', 'GB' ];
        $unit_index = 0;
        while ( $total_bytes >= 1024 && $unit_index < count( $units ) - 1 ) {
            $total_bytes /= 1024;
            $unit_index++;
        }

        return round( $total_bytes, 2 ) . ' ' . $units[$unit_index];
    }

    /**
     * AJAX Action: Clear cached audio files.
     */
    public function ajax_clear_cache() {
        check_ajax_referer( 'readio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'readio' ) );
        }

        $upload_dir = wp_upload_dir();
        $readio_dir = $upload_dir['basedir'] . '/readio';
        
        if ( file_exists( $readio_dir ) ) {
            $files = glob( $readio_dir . '/*.mp3' );
            foreach ( $files as $file ) {
                if ( is_file( $file ) ) {
                    unlink( $file );
                }
            }
        }

        wp_send_json_success([
            'message' => __( 'La caché se ha limpiado correctamente.', 'readio' ),
            'count'   => 0,
            'size'    => '0 B'
        ]);
    }

    /**
     * AJAX Action: Test connection with OpenAI API.
     */
    public function ajax_test_api() {
        check_ajax_referer( 'readio_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'readio' ) );
        }

        $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
        $text    = isset( $_POST['text'] ) ? sanitize_text_field( $_POST['text'] ) : 'Test';
        $voice   = isset( $_POST['voice'] ) ? sanitize_text_field( $_POST['voice'] ) : 'alloy';
        $model   = isset( $_POST['model'] ) ? sanitize_text_field( $_POST['model'] ) : 'tts-1';

        if ( empty( $api_key ) ) {
            wp_send_json_error( __( 'Por favor, proporciona una API Key.', 'readio' ) );
        }

        // Call the TTS API wrapper synchronously
        $tts_handler = new Readio_TTS();
        $audio_data = $tts_handler->fetch_openai_tts( $text, $api_key, $voice, $model );

        if ( is_wp_error( $audio_data ) ) {
            wp_send_json_error( $audio_data->get_error_message() );
        }

        // Return base64 so we can play it directly in the admin dashboard without writing files
        $base64_audio = 'data:audio/mpeg;base64,' . base64_encode( $audio_data );
        
        wp_send_json_success([
            'audio_url' => $base64_audio,
            'message'   => __( '¡Prueba realizada con éxito!', 'readio' )
        ]);
    }
}
