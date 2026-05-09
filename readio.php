<?php
/**
 * Plugin Name: Readio - Visual Reading Time & AI Audio
 * Description: Calculates post reading time with a beautiful progress bar and generates high-quality AI audio versions using OpenAI TTS with local caching, plus native browser speech fallback.
 * Version: 1.1.0
 * Author: Antigravity
 * Text Domain: readio
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define constants
define( 'READIO_VERSION', '1.1.0' );
define( 'READIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'READIO_URL', plugin_dir_url( __FILE__ ) );
define( 'READIO_BASENAME', plugin_basename( __FILE__ ) );

// Include required files
require_once READIO_PATH . 'includes/class-readio-settings.php';
require_once READIO_PATH . 'includes/class-readio-tts.php';
require_once READIO_PATH . 'includes/class-readio-frontend.php';

// Initialize the plugin components
class Readio {
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
            new Readio_Settings();
        }
        
        // Initialize TTS logic (AJAX handlers, API calls, caching)
        new Readio_TTS();

        // Initialize frontend display & assets enqueueing
        new Readio_Frontend();

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
        $readio_dir = $upload_dir['basedir'] . '/readio';
        if ( ! file_exists( $readio_dir ) ) {
            wp_mkdir_p( $readio_dir );
        }
        
        // Add index.php for security
        if ( ! file_exists( $readio_dir . '/index.php' ) ) {
            file_put_contents( $readio_dir . '/index.php', '<?php // Silence is golden' );
        }

        // Add htaccess for folder integrity if needed
        if ( ! file_exists( $readio_dir . '/.htaccess' ) ) {
            file_put_contents( $readio_dir . '/.htaccess', "Options -Indexes\n<Files *.mp3>\n    ForceType audio/mpeg\n</Files>" );
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
function readio_init() {
    return Readio::get_instance();
}
readio_init();
