<?php
/**
 * Sangar Studio ReadFlow Settings Page Class
 * Handles plugin configuration and administration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sangar_Studio_ReadFlow_Settings {

    public function __construct() {
        // Menu creation
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        
        // Register options
        add_action( 'admin_init', [ $this, 'register_plugin_settings' ] );
        
        // Enqueue styles/scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        
        // AJAX: Clear audio cache
        add_action( 'wp_ajax_sangar_readflow_clear_cache', [ $this, 'ajax_clear_cache' ] );
        
        // AJAX: Test API Connection
        add_action( 'wp_ajax_sangar_readflow_test_api', [ $this, 'ajax_test_api' ] );
    }

    /**
     * Add settings page under Settings menu.
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'Sangar Studio ReadFlow Settings', 'sangar-studio-readflow' ),
            'ReadFlow 🎙️',
            'manage_options',
            'sangar-readflow-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings in WordPress.
     */
    public function register_plugin_settings() {
        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_api_key', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_api_key' ],
            'default'           => '',
        ]);
        
        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_voice', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_voice' ],
            'default'           => 'alloy',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_model', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_model' ],
            'default'           => 'tts-1',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_wpm', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 200,
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_position', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_position' ],
            'default'           => 'before',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_accent_color', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#6366f1',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_enable_ai', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_auto_generate', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_allow_guest_generation', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_show_download', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_theme_style', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_theme_style' ],
            'default'           => 'glass',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_border_radius', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_border_radius' ],
            'default'           => 'rounded',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_use_gradient', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_accent_color_2', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#818cf8',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_font_family', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_font_family' ],
            'default'           => 'inherit',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_padding_scale', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_padding_scale' ],
            'default'           => 'default',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_icon_style', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_icon_style' ],
            'default'           => 'emoji',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_text_color', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_button_text_color', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#ffffff',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_text_muted_color', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_wave_bars_count', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 5,
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_wave_bars_style', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_wave_bars_style' ],
            'default'           => 'classic',
        ]);

        register_setting( 'sangar_readflow_settings_group', 'sangar_readflow_wave_bars_animation', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_wave_bars_animation' ],
            'default'           => 'energetic',
        ]);
    }

    /**
     * Sanitize API Key securely.
     */
    public function sanitize_api_key( $value ) {
        $value = sanitize_text_field( trim( $value ) );
        if ( 'PROTECTED_KEY_PLACEHOLDER' === $value ) {
            return get_option( 'sangar_readflow_api_key', '' );
        }
        return $value;
    }

    /**
     * Sanitize and validate voice choice.
     */
    public function sanitize_voice( $value ) {
        $allowed = [ 'alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'alloy';
    }

    /**
     * Sanitize and validate model choice.
     */
    public function sanitize_model( $value ) {
        $allowed = [ 'tts-1', 'tts-1-hd' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'tts-1';
    }

    /**
     * Sanitize and validate placement position.
     */
    public function sanitize_position( $value ) {
        $allowed = [ 'before', 'after', 'both', 'manual' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'before';
    }

    /**
     * Sanitize and validate theme style.
     */
    public function sanitize_theme_style( $value ) {
        $allowed = [ 'glass', 'dark', 'light', 'flat', 'brutalism' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'glass';
    }

    /**
     * Sanitize and validate border radius.
     */
    public function sanitize_border_radius( $value ) {
        $allowed = [ 'sharp', 'rounded', 'pill' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'rounded';
    }

    /**
     * Sanitize and validate font family.
     */
    public function sanitize_font_family( $value ) {
        $allowed = [ 'inherit', 'inter', 'playfair', 'outfit' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'inherit';
    }

    /**
     * Sanitize and validate padding scale.
     */
    public function sanitize_padding_scale( $value ) {
        $allowed = [ 'compact', 'default', 'spacious' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'default';
    }

    /**
     * Sanitize and validate icon style.
     */
    public function sanitize_icon_style( $value ) {
        $allowed = [ 'emoji', 'fontawesome', 'material', 'none' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'emoji';
    }

    /**
     * Sanitize and validate wave bars style.
     */
    public function sanitize_wave_bars_style( $value ) {
        $allowed = [ 'classic', 'symmetric', 'rounded', 'brutalist' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'classic';
    }

    /**
     * Sanitize and validate wave bars animation.
     */
    public function sanitize_wave_bars_animation( $value ) {
        $allowed = [ 'energetic', 'chill', 'none' ];
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : 'energetic';
    }

    /**
     * Enqueue asset files in Admin dashboard.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_sangar-readflow-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'sangar-readflow-admin-style', SANGAR_STUDIO_READFLOW_URL . 'assets/css/admin.css', [], SANGAR_STUDIO_READFLOW_VERSION );
        
        wp_enqueue_script( 'sangar-readflow-admin-script', SANGAR_STUDIO_READFLOW_URL . 'assets/js/admin.js', [ 'jquery', 'wp-color-picker' ], SANGAR_STUDIO_READFLOW_VERSION, true );
        
        wp_localize_script( 'sangar-readflow-admin-script', 'sangar_readflow_admin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'sangar_readflow_admin_nonce' ),
            'loading_text' => __( 'Procesando...', 'sangar-studio-readflow' ),
            'test_success' => __( '¡Conexión exitosa! Reproduciendo audio de prueba...', 'sangar-studio-readflow' ),
            'test_fail' => __( 'Error en la conexión. Revisa tu API Key y saldo.', 'sangar-studio-readflow' )
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
        $api_key                = get_option( 'sangar_readflow_api_key', '' );
        $voice                  = get_option( 'sangar_readflow_voice', 'alloy' );
        $model                  = get_option( 'sangar_readflow_model', 'tts-1' );
        $wpm                    = get_option( 'sangar_readflow_wpm', 200 );
        $position               = get_option( 'sangar_readflow_position', 'before' );
        $accent_color           = get_option( 'sangar_readflow_accent_color', '#6366f1' );
        $accent_color_2         = get_option( 'sangar_readflow_accent_color_2', '#818cf8' );
        $use_gradient           = get_option( 'sangar_readflow_use_gradient', false );
        $theme_style            = get_option( 'sangar_readflow_theme_style', 'glass' );
        $border_radius          = get_option( 'sangar_readflow_border_radius', 'rounded' );
        $font_family            = get_option( 'sangar_readflow_font_family', 'inherit' );
        $padding_scale          = get_option( 'sangar_readflow_padding_scale', 'default' );
        $icon_style             = get_option( 'sangar_readflow_icon_style', 'emoji' );
        $text_color             = get_option( 'sangar_readflow_text_color', '' );
        $text_muted_color       = get_option( 'sangar_readflow_text_muted_color', '' );
        $button_text_color      = get_option( 'sangar_readflow_button_text_color', '#ffffff' );
        $enable_ai              = get_option( 'sangar_readflow_enable_ai', true );
        $auto_generate          = get_option( 'sangar_readflow_auto_generate', false );
        $allow_guest_generation = get_option( 'sangar_readflow_allow_guest_generation', false );
        $show_download          = get_option( 'sangar_readflow_show_download', true );
        $wave_bars_count        = get_option( 'sangar_readflow_wave_bars_count', 5 );
        $wave_bars_style        = get_option( 'sangar_readflow_wave_bars_style', 'classic' );
        $wave_bars_animation    = get_option( 'sangar_readflow_wave_bars_animation', 'energetic' );

        // Mask API Key for display if set
        $masked_key = '';
        if ( ! empty( $api_key ) ) {
            $masked_key = 'sk-••••' . substr( $api_key, -4 );
        }
        ?>
        <div class="readio-admin-wrap">
            <header class="readio-header">
                <div class="readio-logo-area">
                    <span class="readio-icon">🎙️</span>
                    <div class="readio-title-block">
                        <h1>Sangar Studio ReadFlow</h1>
                        <p class="readio-tagline"><?php esc_html_e( 'Visual Reading Time & Premium AI Voice Generator', 'sangar-studio-readflow' ); ?></p>
                    </div>
                </div>
                <div class="readio-version-tag">
                    v<?php echo esc_html( SANGAR_STUDIO_READFLOW_VERSION ); ?>
                </div>
            </header>

            <div class="readio-container">
                <main class="readio-main-content">
                    <form method="post" action="options.php" class="readio-settings-form">
                        <?php settings_fields( 'sangar_readflow_settings_group' ); ?>
                        
                        <!-- TAB CARD 1: API SETTINGS -->
                        <div class="readio-card">
                            <div class="readio-card-header">
                                <span class="card-icon">🔑</span>
                                <h2><?php esc_html_e( 'Configuración de Voz por Inteligencia Artificial', 'sangar-studio-readflow' ); ?></h2>
                            </div>
                            <div class="readio-card-body">
                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_enable_ai">
                                        <?php esc_html_e( 'Activar Voz por IA (OpenAI)', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <label class="readio-switch">
                                            <input type="checkbox" name="sangar_readflow_enable_ai" id="readio_enable_ai" value="1" <?php checked( $enable_ai, true ); ?>>
                                            <span class="readio-slider"></span>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Si está desactivado, el plugin usará automáticamente la voz nativa del navegador (gratuita) sin realizar llamadas API.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-conditional-ai-fields" style="<?php echo $enable_ai ? '' : 'display:none;'; ?>">
                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_api_key_input">
                                            <?php esc_html_e( 'OpenAI API Key', 'sangar-studio-readflow' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <div class="readio-api-input-container">
                                                <input type="password" 
                                                       name="sangar_readflow_api_key" 
                                                       id="readio_api_key_input" 
                                                       value="<?php echo ! empty( $api_key ) ? 'PROTECTED_KEY_PLACEHOLDER' : ''; ?>" 
                                                       placeholder="<?php echo ! empty( $masked_key ) ? esc_attr( $masked_key ) : 'sk-...'; ?>"
                                                       class="regular-text readio-input" />
                                                <button type="button" class="readio-toggle-password" id="readio-toggle-pw-btn">👁️</button>
                                            </div>
                                            <p class="description">
                                                <?php 
                                                echo wp_kses(
                                                    sprintf( 
                                                        esc_html__( 'Inserta tu API key de OpenAI. Puedes obtener una en tu %s.', 'sangar-studio-readflow' ), 
                                                        '<a href="' . esc_url( 'https://platform.openai.com/api-keys' ) . '" target="_blank">' . esc_html__( 'Consola de OpenAI', 'sangar-studio-readflow' ) . '</a>' 
                                                    ),
                                                    [
                                                        'a' => [
                                                            'href'   => [],
                                                            'target' => [],
                                                        ],
                                                    ]
                                                );
                                                ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_voice">
                                            <?php esc_html_e( 'Voz predeterminada de IA', 'sangar-studio-readflow' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <select name="sangar_readflow_voice" id="readio_voice" class="readio-select">
                                                <option value="alloy" <?php selected( $voice, 'alloy' ); ?>>Alloy (<?php esc_html_e( 'Voz balanceada / Neutra', 'sangar-studio-readflow' ); ?>)</option>
                                                <option value="echo" <?php selected( $voice, 'echo' ); ?>>Echo (<?php esc_html_e( 'Voz cálida / Masculina', 'sangar-studio-readflow' ); ?>)</option>
                                                <option value="fable" <?php selected( $voice, 'fable' ); ?>>Fable (<?php esc_html_e( 'Voz expresiva / Dramática', 'sangar-studio-readflow' ); ?>)</option>
                                                <option value="onyx" <?php selected( $voice, 'onyx' ); ?>>Onyx (<?php esc_html_e( 'Voz profunda / Masculina', 'sangar-studio-readflow' ); ?>)</option>
                                                <option value="nova" <?php selected( $voice, 'nova' ); ?>>Nova (<?php esc_html_e( 'Voz enérgica / Femenina', 'sangar-studio-readflow' ); ?>)</option>
                                                <option value="shimmer" <?php selected( $voice, 'shimmer' ); ?>>Shimmer (<?php esc_html_e( 'Voz clara / Femenina', 'sangar-studio-readflow' ); ?>)</option>
                                            </select>
                                            <p class="description"><?php esc_html_e( 'Selecciona la voz que más encaje con el tono de tu contenido. Todas están optimizadas para múltiples idiomas.', 'sangar-studio-readflow' ); ?></p>
                                        </div>
                                    </div>

                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_model">
                                            <?php esc_html_e( 'Modelo TTS de OpenAI', 'sangar-studio-readflow' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <select name="sangar_readflow_model" id="readio_model" class="readio-select">
                                                <option value="tts-1" <?php selected( $model, 'tts-1' ); ?>>tts-1 (<?php esc_html_e( 'Rápido y de baja latencia - Recomendado', 'sangar-studio-readflow' ); ?>)</option>
                                                <option value="tts-1-hd" <?php selected( $model, 'tts-1-hd' ); ?>>tts-1-hd (<?php esc_html_e( 'Alta definición - Calidad superior pero mayor coste', 'sangar-studio-readflow' ); ?>)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_auto_generate">
                                            <?php esc_html_e( 'Pre-generar audio', 'sangar-studio-readflow' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <label class="readio-switch">
                                                <input type="checkbox" name="sangar_readflow_auto_generate" id="readio_auto_generate" value="1" <?php checked( $auto_generate, true ); ?>>
                                                <span class="readio-slider"></span>
                                            </label>
                                            <p class="description"><?php esc_html_e( 'Generar automáticamente el archivo MP3 del post al guardarlo o publicarlo. Esto evita tiempos de espera para el primer lector de la entrada.', 'sangar-studio-readflow' ); ?></p>
                                        </div>
                                    </div>

                                    <div class="readio-form-row">
                                        <label class="readio-label" for="readio_allow_guest_generation">
                                            <?php esc_html_e( 'Permitir generación a invitados', 'sangar-studio-readflow' ); ?>
                                        </label>
                                        <div class="readio-input-wrap">
                                            <label class="readio-switch">
                                                <input type="checkbox" name="sangar_readflow_allow_guest_generation" id="readio_allow_guest_generation" value="1" <?php checked( $allow_guest_generation, true ); ?>>
                                                <span class="readio-slider"></span>
                                            </label>
                                            <p class="description"><?php esc_html_e( 'Permitir a los usuarios invitados (no autenticados) desencadenar la generación de audio con OpenAI si este no está pregenerado. Desmarcar para proteger tu cuota mensual contra abuso o bots.', 'sangar-studio-readflow' ); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB CARD 2: FRONTEND SETTINGS -->
                        <div class="readio-card">
                            <div class="readio-card-header">
                                <span class="card-icon">🎨</span>
                                <h2><?php esc_html_e( 'Personalización del Widget Visual', 'sangar-studio-readflow' ); ?></h2>
                            </div>
                            <div class="readio-card-body">
                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_wpm">
                                        <?php esc_html_e( 'Velocidad de Lectura (Palabras por minuto)', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <div class="readio-range-container">
                                            <input type="range" name="sangar_readflow_wpm" id="readio_wpm" min="100" max="400" step="10" value="<?php echo esc_attr( $wpm ); ?>" class="readio-range">
                                            <span id="readio_wpm_val" class="readio-badge"><?php echo esc_html( $wpm ); ?> PPM</span>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'El promedio de lectura humana es de 200 PPM para textos estándar y 250 PPM para lectura rápida.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_position">
                                        <?php esc_html_e( 'Ubicación en el Post', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_position" id="readio_position" class="readio-select">
                                            <option value="before" <?php selected( $position, 'before' ); ?>><?php esc_html_e( 'Antes del contenido (Recomendado)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="after" <?php selected( $position, 'after' ); ?>><?php esc_html_e( 'Después del contenido', 'sangar-studio-readflow' ); ?></option>
                                            <option value="both" <?php selected( $position, 'both' ); ?>><?php esc_html_e( 'Antes y después del contenido', 'sangar-studio-readflow' ); ?></option>
                                            <option value="manual" <?php selected( $position, 'manual' ); ?>><?php esc_html_e( 'Insertar manualmente vía Shortcode o PHP', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description">
                                            <?php 
                                            echo wp_kses(
                                                sprintf( 
                                                    esc_html__( 'Si eliges "Insertar manualmente", puedes pegar el shortcode %s o llamar a %s en tus plantillas.', 'sangar-studio-readflow' ), 
                                                    '<code>[readio]</code>', 
                                                    '<code>echo do_shortcode(\'[readio]\');</code>' 
                                                ),
                                                [
                                                    'code' => [],
                                                ]
                                            );
                                            ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_theme_style">
                                        <?php esc_html_e( 'Estilo del Tema', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_theme_style" id="readio_theme_style" class="readio-select">
                                            <option value="glass" <?php selected( $theme_style, 'glass' ); ?>><?php esc_html_e( 'Glassmorphism (Vidrio translúcido) - Recomendado', 'sangar-studio-readflow' ); ?></option>
                                            <option value="light" <?php selected( $theme_style, 'light' ); ?>><?php esc_html_e( 'Light Mode (Modo claro limpio)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="dark" <?php selected( $theme_style, 'dark' ); ?>><?php esc_html_e( 'Dark Mode (Sleek nocturno)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="flat" <?php selected( $theme_style, 'flat' ); ?>><?php esc_html_e( 'Flat / Minimalist (Sin bordes ni fondos)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="brutalism" <?php selected( $theme_style, 'brutalism' ); ?>><?php esc_html_e( 'Brutalism (Diseño agresivo y retro)', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Selecciona el fondo y las sombras del widget. Las opciones Glassmorphism y Light Mode se adaptarán automáticamente si el visitante usa dark mode en su sistema operativo.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_icon_style">
                                        <?php esc_html_e( 'Estilo de Iconos', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_icon_style" id="readio_icon_style" class="readio-select">
                                            <option value="emoji" <?php selected( $icon_style, 'emoji' ); ?>><?php esc_html_e( 'Emojis (Nativo)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="fontawesome" <?php selected( $icon_style, 'fontawesome' ); ?>><?php esc_html_e( 'FontAwesome', 'sangar-studio-readflow' ); ?></option>
                                            <option value="material" <?php selected( $icon_style, 'material' ); ?>><?php esc_html_e( 'Material Icons', 'sangar-studio-readflow' ); ?></option>
                                            <option value="none" <?php selected( $icon_style, 'none' ); ?>><?php esc_html_e( 'Ninguno', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Selecciona la librería de iconos para las estadísticas.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_border_radius">
                                        <?php esc_html_e( 'Estilo de Esquinas', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_border_radius" id="readio_border_radius" class="readio-select">
                                            <option value="sharp" <?php selected( $border_radius, 'sharp' ); ?>><?php esc_html_e( 'Sharp (Esquinas cuadradas retro)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="rounded" <?php selected( $border_radius, 'rounded' ); ?>><?php esc_html_e( 'Rounded (Esquinas suaves redondeadas)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="pill" <?php selected( $border_radius, 'pill' ); ?>><?php esc_html_e( 'Pill (Forma de píldora moderna)', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Define las curvaturas del widget principal y de los botones internos.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_wave_bars_count">
                                        <?php esc_html_e( 'Cantidad de Barras de Sonido', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_wave_bars_count" id="readio_wave_bars_count" class="readio-select">
                                            <option value="5" <?php selected( $wave_bars_count, 5 ); ?>>5 <?php esc_html_e( 'barras (Clásico compacto)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="8" <?php selected( $wave_bars_count, 8 ); ?>>8 <?php esc_html_e( 'barras (Equilibrado)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="12" <?php selected( $wave_bars_count, 12 ); ?>>12 <?php esc_html_e( 'barras (Elegante)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="16" <?php selected( $wave_bars_count, 16 ); ?>>16 <?php esc_html_e( 'barras (Lleno/Premium)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="20" <?php selected( $wave_bars_count, 20 ); ?>>20 <?php esc_html_e( 'barras (Ultra Alta Fidelidad)', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Selecciona el número de barras que formarán la onda visual del reproductor.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_wave_bars_style">
                                        <?php esc_html_e( 'Estilo Visual del Ecualizador', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_wave_bars_style" id="readio_wave_bars_style" class="readio-select">
                                            <option value="classic" <?php selected( $wave_bars_style, 'classic' ); ?>><?php esc_html_e( 'Líneas clásicas (crece desde abajo)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="symmetric" <?php selected( $wave_bars_style, 'symmetric' ); ?>><?php esc_html_e( 'Onda simétrica (onda real desde el centro)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="rounded" <?php selected( $wave_bars_style, 'rounded' ); ?>><?php esc_html_e( 'Píldoras redondeadas', 'sangar-studio-readflow' ); ?></option>
                                            <option value="brutalist" <?php selected( $wave_bars_style, 'brutalist' ); ?>><?php esc_html_e( 'Bloques brutalistas (esquinas rectas sin margen)', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Personaliza la forma y origen de escala de las barras de sonido.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_wave_bars_animation">
                                        <?php esc_html_e( 'Animación del Visualizador', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_wave_bars_animation" id="readio_wave_bars_animation" class="readio-select">
                                            <option value="energetic" <?php selected( $wave_bars_animation, 'energetic' ); ?>><?php esc_html_e( 'Enérgica (Rápida y dinámica)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="chill" <?php selected( $wave_bars_animation, 'chill' ); ?>><?php esc_html_e( 'Relajada (Suave y cadenciosa)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="none" <?php selected( $wave_bars_animation, 'none' ); ?>><?php esc_html_e( 'Estática (Sin animación, sólo indicador visual)', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Define la velocidad y el dinamismo con el que vibrarán las barras de sonido cuando se esté reproduciendo el audio.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_font_family">
                                        <?php esc_html_e( 'Tipografía del Reproductor', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_font_family" id="readio_font_family" class="readio-select">
                                            <option value="inherit" <?php selected( $font_family, 'inherit' ); ?>><?php esc_html_e( 'Heredada (Usar tipografía de tu tema)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="inter" <?php selected( $font_family, 'inter' ); ?>>Inter (<?php esc_html_e( 'Moderna sans-serif premium', 'sangar-studio-readflow' ); ?>)</option>
                                            <option value="playfair" <?php selected( $font_family, 'playfair' ); ?>>Playfair Display (<?php esc_html_e( 'Elegante serif clásica', 'sangar-studio-readflow' ); ?>)</option>
                                            <option value="outfit" <?php selected( $font_family, 'outfit' ); ?>>Outfit (<?php esc_html_e( 'Geométrica y enérgica redondeada', 'sangar-studio-readflow' ); ?>)</option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Permite cambiar la tipografía para dar un toque de diseño exclusivo. El plugin cargará las fuentes optimizadas desde Google Fonts si eliges una distinta a la de tu tema.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_padding_scale">
                                        <?php esc_html_e( 'Tamaño del Widget', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <select name="sangar_readflow_padding_scale" id="readio_padding_scale" class="readio-select">
                                            <option value="compact" <?php selected( $padding_scale, 'compact' ); ?>><?php esc_html_e( 'Compacto (Márgenes ajustados)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="default" <?php selected( $padding_scale, 'default' ); ?>><?php esc_html_e( 'Predeterminado (Equilibrado)', 'sangar-studio-readflow' ); ?></option>
                                            <option value="spacious" <?php selected( $padding_scale, 'spacious' ); ?>><?php esc_html_e( 'Espacioso (Padding amplio y elegante)', 'sangar-studio-readflow' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Ajusta los espacios internos (padding) para integrarse con la densidad de diseño de tus plantillas de blog.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_accent_color">
                                        <?php esc_html_e( 'Color de acento primario', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <input type="text" name="sangar_readflow_accent_color" id="readio_accent_color" value="<?php echo esc_attr( $accent_color ); ?>" class="readio-color-field" data-default-color="#6366f1">
                                        <p class="description"><?php esc_html_e( 'Define el color de los botones del reproductor, barras de progreso y elementos interactivos primarios.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_text_color">
                                        <?php esc_html_e( 'Color del Texto (Opcional)', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <input type="text" name="sangar_readflow_text_color" id="readio_text_color" value="<?php echo esc_attr( $text_color ); ?>" class="readio-color-field" data-default-color="">
                                        <p class="description"><?php esc_html_e( 'Sobrescribe el color del texto principal del widget. Déjalo en blanco para usar el color por defecto del tema.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_text_muted_color">
                                        <?php esc_html_e( 'Color del Texto Secundario', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <input type="text" name="sangar_readflow_text_muted_color" id="readio_text_muted_color" value="<?php echo esc_attr( $text_muted_color ); ?>" class="readio-color-field" data-default-color="">
                                        <p class="description"><?php esc_html_e( 'Color de textos secundarios como la velocidad, indicadores y tiempo. Déjalo en blanco para usar el color por defecto.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_button_text_color">
                                        <?php esc_html_e( 'Color del Texto del Botón', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <input type="text" name="sangar_readflow_button_text_color" id="readio_button_text_color" value="<?php echo esc_attr( $button_text_color ); ?>" class="readio-color-field" data-default-color="#ffffff">
                                        <p class="description"><?php esc_html_e( 'Color de la fuente dentro del botón de reproducción principal.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_use_gradient">
                                        <?php esc_html_e( 'Usar Gradiente de Color', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <label class="readio-switch">
                                            <input type="checkbox" name="sangar_readflow_use_gradient" id="readio_use_gradient" value="1" <?php checked( $use_gradient, true ); ?>>
                                            <span class="readio-slider"></span>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Genera un degradado premium de dos colores en los elementos destacados del reproductor visual.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row readio-conditional-gradient-row" style="<?php echo $use_gradient ? '' : 'display:none;'; ?>">
                                    <label class="readio-label" for="readio_accent_color_2">
                                        <?php esc_html_e( 'Color de acento secundario', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <input type="text" name="sangar_readflow_accent_color_2" id="readio_accent_color_2" value="<?php echo esc_attr( $accent_color_2 ); ?>" class="readio-color-field" data-default-color="#818cf8">
                                        <p class="description"><?php esc_html_e( 'Selecciona el segundo color para crear la transición lineal del degradado.', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>

                                <div class="readio-form-row">
                                    <label class="readio-label" for="readio_show_download">
                                        <?php esc_html_e( 'Permitir descarga de Audio', 'sangar-studio-readflow' ); ?>
                                    </label>
                                    <div class="readio-input-wrap">
                                        <label class="readio-switch">
                                            <input type="checkbox" name="sangar_readflow_show_download" id="readio_show_download" value="1" <?php checked( $show_download, true ); ?>>
                                            <span class="readio-slider"></span>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Muestra un enlace discreto de descarga en el reproductor personalizado (solo disponible para voz IA).', 'sangar-studio-readflow' ); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT ACTIONS -->
                        <div class="readio-submit-area">
                            <?php submit_button( __( 'Guardar Configuración', 'sangar-studio-readflow' ), 'primary readio-btn-save' ); ?>
                        </div>
                    </form>
                </main>

                <!-- ASIDE: API TESTER & UTILITIES -->
                <aside class="readio-sidebar">
                    <!-- CARD 3: TESTER -->
                    <div class="readio-card readio-tester-card" id="readio-tester-box" style="<?php echo $enable_ai ? '' : 'display:none;'; ?>">
                        <div class="readio-card-header">
                            <span class="card-icon">⚡</span>
                            <h2><?php esc_html_e( 'Probar Conexión OpenAI', 'sangar-studio-readflow' ); ?></h2>
                        </div>
                        <div class="readio-card-body">
                            <p class="tester-intro"><?php esc_html_e( 'Genera un audio de prueba al instante para verificar tus credenciales y escuchar el resultado.', 'sangar-studio-readflow' ); ?></p>
                            <div class="readio-tester-form">
                                <textarea id="readio-test-text" class="readio-textarea" placeholder="<?php esc_attr_e( 'Escribe unas pocas palabras aquí...', 'sangar-studio-readflow' ); ?>"><?php esc_html_e( '¡Hola! Sangar Studio ReadFlow está configurado correctamente y listo para dar voz a tus posts.', 'sangar-studio-readflow' ); ?></textarea>
                                <button type="button" id="readio-btn-test-api" class="button readio-btn-accent">
                                    <span>🎙️ <?php esc_html_e( 'Probar ahora', 'sangar-studio-readflow' ); ?></span>
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
                            <h2><?php esc_html_e( 'Mantenimiento del Plugin', 'sangar-studio-readflow' ); ?></h2>
                        </div>
                        <div class="readio-card-body">
                            <p><?php esc_html_e( 'Los archivos de audio de IA generados se guardan localmente para no repetir peticiones costosas a OpenAI.', 'sangar-studio-readflow' ); ?></p>
                            <div class="readio-stats-row">
                                <span class="stats-label"><?php esc_html_e( 'Audios en Caché:', 'sangar-studio-readflow' ); ?></span>
                                <strong id="readio-cache-count"><?php echo esc_html( self::get_cached_audios_count() ); ?></strong>
                            </div>
                            <div class="readio-stats-row">
                                <span class="stats-label"><?php esc_html_e( 'Espacio Ocupado:', 'sangar-studio-readflow' ); ?></span>
                                <strong id="readio-cache-size"><?php echo esc_html( self::get_cached_audios_size() ); ?></strong>
                            </div>
                            <button type="button" id="readio-btn-clear-cache" class="button readio-btn-danger">
                                🗑️ <?php esc_html_e( 'Limpiar Todos los Audios', 'sangar-studio-readflow' ); ?>
                            </button>
                            <p class="description danger-description"><?php esc_html_e( 'Atención: Al borrar el caché se eliminarán todos los archivos MP3 locales. Los audios se volverán a generar la próxima vez que los usuarios los soliciten.', 'sangar-studio-readflow' ); ?></p>
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
        $readflow_dir = $upload_dir['basedir'] . '/sangar-studio-readflow';
        if ( ! file_exists( $readflow_dir ) ) {
            return 0;
        }

        $files = glob( $readflow_dir . '/*.mp3' );
        return count( $files );
    }

    /**
     * Get total size of locally cached audio MP3 files.
     */
    public static function get_cached_audios_size() {
        $upload_dir = wp_upload_dir();
        $readflow_dir = $upload_dir['basedir'] . '/sangar-studio-readflow';
        if ( ! file_exists( $readflow_dir ) ) {
            return '0 B';
        }

        $files = glob( $readflow_dir . '/*.mp3' );
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
        check_ajax_referer( 'sangar_readflow_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'sangar-studio-readflow' ) );
        }

        $upload_dir = wp_upload_dir();
        $readflow_dir = $upload_dir['basedir'] . '/sangar-studio-readflow';
        
        if ( file_exists( $readflow_dir ) ) {
            $files = glob( $readflow_dir . '/*.mp3' );
            foreach ( $files as $file ) {
                if ( is_file( $file ) ) {
                    wp_delete_file( $file );
                }
            }
        }

        wp_send_json_success([
            'message' => __( 'La caché se ha limpiado correctamente.', 'sangar-studio-readflow' ),
            'count'   => 0,
            'size'    => '0 B'
        ]);
    }

    /**
     * AJAX Action: Test connection with OpenAI API.
     */
    public function ajax_test_api() {
        check_ajax_referer( 'sangar_readflow_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'sangar-studio-readflow' ) );
        }

        $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
        $text    = isset( $_POST['text'] ) ? sanitize_text_field( wp_unslash( $_POST['text'] ) ) : 'Test';
        $voice   = isset( $_POST['voice'] ) ? sanitize_text_field( wp_unslash( $_POST['voice'] ) ) : 'alloy';
        $model   = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : 'tts-1';

        if ( 'PROTECTED_KEY_PLACEHOLDER' === $api_key ) {
            $api_key = get_option( 'sangar_readflow_api_key', '' );
        }

        if ( empty( $api_key ) ) {
            wp_send_json_error( __( 'Por favor, proporciona una API Key.', 'sangar-studio-readflow' ) );
        }

        // Call the TTS API wrapper synchronously
        $tts_handler = new Sangar_Studio_ReadFlow_TTS();
        $audio_data = $tts_handler->fetch_openai_tts( $text, $api_key, $voice, $model );

        if ( is_wp_error( $audio_data ) ) {
            wp_send_json_error( $audio_data->get_error_message() );
        }

        // Return base64 so we can play it directly in the admin dashboard without writing files
        $base64_audio = 'data:audio/mpeg;base64,' . base64_encode( $audio_data );
        
        wp_send_json_success([
            'audio_url' => $base64_audio,
            'message'   => __( '¡Prueba realizada con éxito!', 'sangar-studio-readflow' )
        ]);
    }
}
