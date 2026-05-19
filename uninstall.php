<?php
/**
 * Sangar Studio ReadFlow Uninstall
 * Cleans up options, transients, and cached MP3 files upon plugin deletion.
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// 1. Delete all registered options from the database
$options = [
    'sangar_readflow_api_key',
    'sangar_readflow_voice',
    'sangar_readflow_model',
    'sangar_readflow_wpm',
    'sangar_readflow_position',
    'sangar_readflow_accent_color',
    'sangar_readflow_enable_ai',
    'sangar_readflow_auto_generate',
    'sangar_readflow_allow_guest_generation',
    'sangar_readflow_show_download',
    'sangar_readflow_theme_style',
    'sangar_readflow_border_radius',
    'sangar_readflow_use_gradient',
    'sangar_readflow_accent_color_2',
    'sangar_readflow_font_family',
    'sangar_readflow_padding_scale',
    'sangar_readflow_icon_style',
    'sangar_readflow_text_color',
    'sangar_readflow_button_text_color',
    'sangar_readflow_text_muted_color',
    'sangar_readflow_wave_bars_count',
    'sangar_readflow_wave_bars_style',
    'sangar_readflow_wave_bars_animation',
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

// Clean up new folder
$readflow_dir = $upload_dir['basedir'] . '/sangar-studio-readflow';
if ( file_exists( $readflow_dir ) ) {
    $files = glob( $readflow_dir . '/*' );
    foreach ( $files as $file ) {
        if ( is_file( $file ) ) {
            @unlink( $file );
        }
    }
    @rmdir( $readflow_dir );
}

// Clean up legacy folder just in case
$legacy_dir = $upload_dir['basedir'] . '/readio';
if ( file_exists( $legacy_dir ) ) {
    $files = glob( $legacy_dir . '/*' );
    foreach ( $files as $file ) {
        if ( is_file( $file ) ) {
            @unlink( $file );
        }
    }
    @rmdir( $legacy_dir );
}
