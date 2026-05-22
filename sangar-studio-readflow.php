<?php
/**
 * Plugin Name: Sangar Studio ReadFlow
 * Description: Calculates post reading time with a beautiful progress bar and generates high-quality AI audio versions using OpenAI TTS with local caching, plus native browser speech fallback.
 * Version: 1.2.1
 * Author: Antigravity
 * Text Domain: sangar-studio-readflow
 * Domain Path: /languages
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define constants
define( 'SSRF_VERSION', '1.2.1' );
define( 'SSRF_PATH', plugin_dir_path( __FILE__ ) );
define( 'SSRF_URL', plugin_dir_url( __FILE__ ) );
define( 'SSRF_BASENAME', plugin_basename( __FILE__ ) );

// Include required files
require_once SSRF_PATH . 'includes/class-ssrf-settings.php';
require_once SSRF_PATH . 'includes/class-ssrf-tts.php';
require_once SSRF_PATH . 'includes/class-ssrf-frontend.php';

/**
 * Initialize the plugin components
 */
class SSRF {
    private static $instance = null;

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize settings page in admin
        if ( is_admin() ) {
            new SSRF_Settings();
        }
        
        // Initialize TTS logic (AJAX handlers, API calls, caching)
        new SSRF_TTS();

        // Initialize frontend display & assets enqueueing
        new SSRF_Frontend();

        // Register activation & deactivation hooks
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
    }

    /**
     * Set up the plugin on activation.
     */
    public function activate() {
        // Run any setup needed (e.g. create uploads subfolder for caching MP3s)
        $upload_dir = wp_upload_dir();
        $readflow_dir = $upload_dir['basedir'] . '/ssrf';
        if ( ! file_exists( $readflow_dir ) ) {
            wp_mkdir_p( $readflow_dir );
        }
        
        // Add index.php for security
        if ( ! file_exists( $readflow_dir . '/index.php' ) ) {
            file_put_contents( $readflow_dir . '/index.php', '<?php // Silence is golden' );
        }

        // Add htaccess for folder integrity if needed
        if ( ! file_exists( $readflow_dir . '/.htaccess' ) ) {
            file_put_contents( $readflow_dir . '/.htaccess', "Options -Indexes\n<Files *.mp3>\n    ForceType audio/mpeg\n</Files>" );
        }
    }

    /**
     * Clean up on deactivation if needed.
     */
    public function deactivate() {
        // We do not delete the cached MP3 files on deactivation to preserve user assets,
        // but we can flush transient rules if we added any.
    }
}

// Instantiate the plugin
function ssrf_init() {
    return SSRF::get_instance();
}
ssrf_init();
