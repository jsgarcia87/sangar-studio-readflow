<?php
/**
 * Readio Frontend Widget Class
 * Renders the high-fidelity reader widget and enqueues scripts.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Readio_Frontend {

    public function __construct() {
        // Enqueue styles and scripts
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );

        // Automatic widget insertion hook
        add_filter( 'the_content', [ $this, 'insert_widget' ] );

        // Register shortcode
        add_shortcode( 'readio', [ $this, 'render_shortcode' ] );
    }

    /**
     * Calculate reading time based on user-configured Words Per Minute (WPM).
     */
    public function calculate_reading_time( $content ) {
        $wpm = get_option( 'readio_wpm', 200 );
        if ( ! $wpm || $wpm <= 0 ) {
            $wpm = 200;
        }

        // Clean content of tags, shortcodes, and spaces
        $clean_content = strip_tags( strip_shortcodes( $content ) );
        
        // Count words accurately
        $word_count = str_word_count( $clean_content );
        
        // Return rounded up minutes and raw count
        $minutes = ceil( $word_count / $wpm );
        if ( $minutes < 1 && $word_count > 0 ) {
            $minutes = 1;
        }
        
        return [
            'minutes'    => $minutes,
            'word_count' => $word_count
        ];
    }

    /**
     * Enqueue CSS, JS, and dynamically push setting parameters.
     */
    public function enqueue_frontend_assets() {
        if ( ! is_single() ) {
            return;
        }

        // Get option values
        $api_key   = get_option( 'readio_api_key', '' );
        $enable_ai = get_option( 'readio_enable_ai', true ) && ! empty( $api_key );
        $accent    = get_option( 'readio_accent_color', '#6366f1' );

        // Enqueue stylesheet
        wp_enqueue_style( 'readio-frontend-style', READIO_URL . 'assets/css/frontend.css', [], READIO_VERSION );
        
        // Enqueue custom player logic
        wp_enqueue_script( 'readio-frontend-script', READIO_URL . 'assets/js/frontend.js', [], READIO_VERSION, true );

        // Convert HEX accent color to RGB for smooth alpha transparency overlays in CSS
        $accent_rgb = $this->hex2rgb( $accent );

        // Apply inline CSS styles to set CSS custom properties matching administrative layout options
        $custom_css = "
            :root {
                --readio-accent: {$accent};
                --readio-accent-rgb: {$accent_rgb};
            }
        ";
        wp_add_inline_style( 'readio-frontend-style', $custom_css );

        // Pass security tokens, paths, and localized text lines safely to JavaScript
        wp_localize_script( 'readio-frontend-script', 'readio_obj', [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'readio_frontend_nonce' ),
            'post_id'   => get_the_ID(),
            'has_ai'    => $enable_ai,
            'locale'    => get_locale(),
            'text'      => [
                'play'       => __( 'Escuchar Entrada', 'readio' ),
                'pause'      => __( 'Pausar', 'readio' ),
                'generating' => __( 'Generando audio de IA...', 'readio' ),
                'buffering'  => __( 'Cargando...', 'readio' ),
                'playing_ai' => __( 'Reproduciendo Voz IA', 'readio' ),
                'playing_nat'=> __( 'Reproduciendo Voz Local', 'readio' ),
                'error'      => __( 'Fallo al cargar audio.', 'readio' ),
            ]
        ]);
    }

    /**
     * Convert HEX color string to comma-separated RGB values.
     */
    private function hex2rgb( $hex ) {
        $hex = str_replace( "#", "", $hex );
        if ( strlen( $hex ) === 3 ) {
            $r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
            $g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
            $b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
        } else {
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );
        }
        return "$r, $g, $b";
    }

    /**
     * Compile widget HTML markup.
     */
    public function render_widget() {
        $post = get_post();
        if ( ! $post ) {
            return '';
        }

        $read_data = $this->calculate_reading_time( $post->post_content );
        $time      = $read_data['minutes'];
        $words     = $read_data['word_count'];
        
        $api_key   = get_option( 'readio_api_key', '' );
        $has_ai    = get_option( 'readio_enable_ai', true ) && ! empty( $api_key );
        $show_dl   = get_option( 'readio_show_download', true );

        ob_start();
        ?>
        <div class="readio-widget" id="readio-widget-box" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
            <div class="readio-widget-header">
                <div class="readio-stat-pill">
                    <span class="readio-pill-icon">⏱️</span>
                    <span class="readio-pill-text">
                        <?php echo sprintf( esc_html__( 'Tiempo de lectura: %d min', 'readio' ), $time ); ?>
                    </span>
                </div>
                <div class="readio-stat-pill">
                    <span class="readio-pill-icon">✍️</span>
                    <span class="readio-pill-text">
                        <?php echo sprintf( _n( '%s palabra', '%s palabras', $words, 'readio' ), number_format_i18n( $words ) ); ?>
                    </span>
                </div>
            </div>

            <div class="readio-player-body">
                <div class="readio-row">
                    <!-- Premium Action button -->
                    <button type="button" class="readio-action-play" id="readio-play-btn" aria-label="<?php esc_attr_e( 'Escuchar post', 'readio' ); ?>">
                        <span class="readio-play-icon-wrap">
                            <!-- SVG Play -->
                            <svg class="readio-svg-play" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                            </svg>
                            <!-- SVG Pause -->
                            <svg class="readio-svg-pause" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                <path d="M6 19H10V5H6V19ZM14 5V19H18V5H14Z" fill="currentColor"/>
                            </svg>
                            <!-- SVG Loading Spinner -->
                            <svg class="readio-svg-spinner" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="36 12"/>
                            </svg>
                        </span>
                        <span class="readio-play-label" id="readio-play-label"><?php esc_html_e( 'Escuchar Entrada', 'readio' ); ?></span>
                    </button>

                    <!-- Interactive Micro-Animation Sound Wave bars (animates during play) -->
                    <div class="readio-wave" id="readio-wave-animation">
                        <span class="readio-wave-bar"></span>
                        <span class="readio-wave-bar"></span>
                        <span class="readio-wave-bar"></span>
                        <span class="readio-wave-bar"></span>
                        <span class="readio-wave-bar"></span>
                    </div>
                </div>

                <!-- Custom Progress bar Timeline (shown upon load/generation) -->
                <div class="readio-timeline-container" id="readio-timeline" style="display: none;">
                    <span class="readio-time-text" id="readio-current-time">0:00</span>
                    <div class="readio-progress-rail" id="readio-progress-rail">
                        <div class="readio-progress-bar" id="readio-progress-fill"></div>
                        <div class="readio-progress-knob" id="readio-progress-knob"></div>
                    </div>
                    <span class="readio-time-text" id="readio-total-duration">0:00</span>
                </div>

                <!-- Controls row footer -->
                <div class="readio-footer-controls" id="readio-footer-controls" style="display: none;">
                    <!-- Speed configuration button -->
                    <div class="readio-speed-control">
                        <button type="button" class="readio-control-btn" id="readio-speed-toggle-btn" title="<?php esc_attr_e( 'Velocidad de audio', 'readio' ); ?>">
                            <span id="readio-current-speed">1.0x</span>
                        </button>
                        <ul class="readio-speed-menu" id="readio-speed-dropdown">
                            <li data-speed="0.8">0.8x</li>
                            <li data-speed="1.0" class="active">1.0x</li>
                            <li data-speed="1.2">1.2x</li>
                            <li data-speed="1.5">1.5x</li>
                            <li data-speed="2.0">2.0x</li>
                        </ul>
                    </div>

                    <!-- Mode Indicator badge -->
                    <div class="readio-mode-indicator">
                        <span class="readio-indicator-dot"></span>
                        <span class="readio-indicator-text" id="readio-mode-label">
                            <?php echo $has_ai ? esc_html__( 'Voz Inteligente', 'readio' ) : esc_html__( 'Voz del Navegador', 'readio' ); ?>
                        </span>
                    </div>

                    <!-- Direct File Download Link -->
                    <?php if ( $has_ai && $show_dl ) : ?>
                        <a href="#" class="readio-control-btn readio-btn-download" id="readio-btn-download" download title="<?php esc_attr_e( 'Descargar archivo MP3', 'readio' ); ?>" aria-label="<?php esc_attr_e( 'Descargar MP3', 'readio' ); ?>">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                <path d="M5 20H19V18H5V20ZM12 2L12 14M12 14L8 10M12 14L16 10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Native audio tag hidden inside container -->
            <audio id="readio-html5-audio" style="display: none;" preload="none"></audio>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Core filter logic to automatically append or prepend the template to the content.
     */
    public function insert_widget( $content ) {
        // Enforce conditions: single post page, primary loop, core post type
        if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        if ( 'post' !== get_post_type() ) {
            return $content;
        }

        $position = get_option( 'readio_position', 'before' );

        // Manual embedding overrides automated inclusion
        if ( 'manual' === $position ) {
            return $content;
        }

        $widget = $this->render_widget();

        if ( 'before' === $position ) {
            return $widget . $content;
        } elseif ( 'after' === $position ) {
            return $content . $widget;
        } elseif ( 'both' === $position ) {
            return $widget . $content . $widget;
        }

        return $content;
    }

    /**
     * Shortcode execution callback mapping.
     */
    public function render_shortcode() {
        return $this->render_widget();
    }
}
