<?php
/**
 * Sangar Studio ReadFlow Uninstall
 * Cleans up options, transients, and cached MP3 files upon plugin deletion.
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Perform all cleanup actions upon plugin uninstall.
 */
function ssrf_uninstall_plugin() {
    // 1. Delete all registered options from the database
    $options = [
        'ssrf_api_key',
        'ssrf_voice',
        'ssrf_model',
        'ssrf_wpm',
        'ssrf_position',
        'ssrf_accent_color',
        'ssrf_enable_ai',
        'ssrf_auto_generate',
        'ssrf_allow_guest_generation',
        'ssrf_show_download',
        'ssrf_theme_style',
        'ssrf_border_radius',
        'ssrf_use_gradient',
        'ssrf_accent_color_2',
        'ssrf_font_family',
        'ssrf_padding_scale',
        'ssrf_icon_style',
        'ssrf_text_color',
        'ssrf_button_text_color',
        'ssrf_text_muted_color',
        'ssrf_wave_bars_count',
        'ssrf_wave_bars_style',
        'ssrf_wave_bars_animation',
        // Legacy options just in case they were migrated
        'readio_api_key',
        'readio_voice',
        'readio_model',
        'readio_wpm',
        'readio_position',
        'readio_accent_color',
        'readio_enable_ai',
        'readio_auto_generate',
        'readio_allow_guest_generation',
        'readio_show_download',
        'readio_theme_style',
        'readio_border_radius',
        'readio_use_gradient',
        'readio_accent_color_2',
        'readio_font_family',
        'readio_padding_scale',
        'readio_icon_style',
        'readio_text_color',
        'readio_button_text_color',
        'readio_text_muted_color',
        'readio_wave_bars_count',
        'readio_wave_bars_style',
        'readio_wave_bars_animation',
    ];

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // 2. Clean up cached audio files inside wp-content/uploads/
    $upload_dir = wp_upload_dir();
    $readflow_dir = $upload_dir['basedir'] . '/ssrf';
    $legacy_dir   = $upload_dir['basedir'] . '/readio';

    // Initialize WP_Filesystem to delete directories safely without raw PHP calls
    global $wp_filesystem;
    if ( empty( $wp_filesystem ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    if ( ! empty( $wp_filesystem ) ) {
        if ( $wp_filesystem->exists( $readflow_dir ) ) {
            $wp_filesystem->delete( $readflow_dir, true );
        }
        if ( $wp_filesystem->exists( $legacy_dir ) ) {
            $wp_filesystem->delete( $legacy_dir, true );
        }
    }
}

ssrf_uninstall_plugin();
