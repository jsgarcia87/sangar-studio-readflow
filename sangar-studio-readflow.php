<?php
/**
 * Plugin Name: Sangar Studio ReadFlow
 * Description: Calculates post reading time with a beautiful progress bar and generates high-quality AI audio versions using OpenAI TTS with local caching, plus native browser speech fallback.
 * Version: 1.1.0
 * Author: Antigravity
 * Text Domain: sangar-studio-readflow
 * Domain Path: /languages
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define constants
define( 'SANGAR_STUDIO_READFLOW_VERSION', '1.1.0' );
define( 'SANGAR_STUDIO_READFLOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'SANGAR_STUDIO_READFLOW_URL', plugin_dir_url( __FILE__ ) );
define( 'SANGAR_STUDIO_READFLOW_BASENAME', plugin_basename( __FILE__ ) );

// Include required files
require_once SANGAR_STUDIO_READFLOW_PATH . 'includes/class-sangar-studio-readflow-settings.php';
require_once SANGAR_STUDIO_READFLOW_PATH . 'includes/class-sangar-studio-readflow-tts.php';
require_once SANGAR_STUDIO_READFLOW_PATH . 'includes/class-sangar-studio-readflow-frontend.php';

/**
 * Initialize the plugin components
 */
class Sangar_Studio_ReadFlow {
    private static $instance = null;

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Load translations
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // Initialize settings page in admin
        if ( is_admin() ) {
            new Sangar_Studio_ReadFlow_Settings();
        }
        
        // Initialize TTS logic (AJAX handlers, API calls, caching)
        new Sangar_Studio_ReadFlow_TTS();

        // Initialize frontend display & assets enqueueing
        new Sangar_Studio_ReadFlow_Frontend();

        // Register activation & deactivation hooks
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'sangar-studio-readflow', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Set up the plugin on activation.
     */
    public function activate() {
        // Run any setup needed (e.g. create uploads subfolder for caching MP3s)
        $upload_dir = wp_upload_dir();
        $readflow_dir = $upload_dir['basedir'] . '/sangar-studio-readflow';
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
function sangar_studio_readflow_init() {
    return Sangar_Studio_ReadFlow::get_instance();
}
sangar_studio_readflow_init();
