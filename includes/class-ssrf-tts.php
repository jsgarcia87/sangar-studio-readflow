<?php
/**
 * Sangar Studio ReadFlow TTS Handler Class
 * Handles speech generation, split-chunking, binary concatenation, and local caching.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SSRF_TTS {

    public function __construct() {
        // AJAX: Retrieve cached audio or generate on-the-fly
        add_action( 'wp_ajax_ssrf_get_audio', [ $this, 'ajax_get_audio' ] );
        add_action( 'wp_ajax_nopriv_ssrf_get_audio', [ $this, 'ajax_get_audio' ] );

        // Invalidating and pre-generating cached audio on post updates
        add_action( 'save_post', [ $this, 'handle_save_post' ], 10, 3 );

        // WordPress 7.0 Abilities API Native AI Integration (Safe-guarded)
        add_action( 'wp_abilities_api_categories_init', [ $this, 'register_abilities_category' ] );
        add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
    }

    /**
     * Splits a text string into logical sentence-level chunks, 
     * staying strictly below OpenAI's 4096 character limit (e.g., 3500 max).
     */
    public function chunk_text( $text, $max_length = 3500 ) {
        if ( mb_strlen( $text ) <= $max_length ) {
            return [ $text ];
        }

        $chunks = [];
        $text = trim( $text );

        while ( mb_strlen( $text ) > 0 ) {
            if ( mb_strlen( $text ) <= $max_length ) {
                $chunks[] = $text;
                break;
            }

            // Slice out a substring of exactly max_length to examine where to break
            $substring = mb_substr( $text, 0, $max_length );
            
            // Try splitting on sentence boundaries to keep high semantic quality
            $split_pos = false;
            $delimiters = [ '. ', '? ', '! ', ".\n", "?\n", "!\n" ];
            foreach ( $delimiters as $delimiter ) {
                $pos = mb_strrpos( $substring, $delimiter );
                if ( $pos !== false && $pos > $max_length * 0.5 ) { // Ensure the chunk is reasonably filled
                    if ( $split_pos === false || $pos > $split_pos ) {
                        // Include the delimiter inside the chunk
                        $split_pos = $pos + mb_strlen( $delimiter ) - 1;
                    }
                }
            }

            // If no clean sentence delimiter is found, fall back to spacing
            if ( $split_pos === false ) {
                $pos = mb_strrpos( $substring, ' ' );
                if ( $pos !== false && $pos > $max_length * 0.3 ) {
                    $split_pos = $pos;
                }
            }

            // Absolute fallback: cut exactly at limit
            if ( $split_pos === false ) {
                $split_pos = $max_length;
            }

            // Add the chunk and trim the remaining text to process
            $chunks[] = trim( mb_substr( $text, 0, $split_pos ) );
            $text = trim( mb_substr( $text, $split_pos ) );
        }

        return $chunks;
    }

    /**
     * Calls OpenAI TTS API for one or more text chunks, 
     * and concatenates the resulting binary stream outputs.
     */
    public function fetch_openai_tts( $text, $api_key, $voice = 'alloy', $model = 'tts-1' ) {
        $chunks = $this->chunk_text( $text, 3500 );
        $combined_audio = '';

        foreach ( $chunks as $chunk ) {
            if ( empty( $chunk ) ) {
                continue;
            }

            $response = wp_remote_post( 'https://api.openai.com/v1/audio/speech', [
                'timeout'     => 30, // Speech generation can take some time
                'headers'     => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body'        => wp_json_encode([
                    'model' => $model,
                    'input' => $chunk,
                    'voice' => $voice,
                ]),
            ]);

            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );

            if ( $code !== 200 ) {
                /* translators: %d: HTTP status code */
                $error_msg = sprintf( __( 'Error en API de OpenAI (Código HTTP %d)', 'sangar-studio-readflow' ), $code );
                $json = json_decode( $body, true );
                if ( ! empty( $json['error']['message'] ) ) {
                    $error_msg = $json['error']['message'];
                }
                return new WP_Error( 'openai_api_error', $error_msg );
            }

            // Concatenate binary MP3 chunk stream directly.
            // MP3 files of identical bitrate can be appended at the file level safely.
            $combined_audio .= $body;
        }

        return $combined_audio;
    }

    /**
     * Generates and caches an MP3 audio version for a given post.
     */
    public function generate_audio_for_post( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'invalid_post', __( 'El post no existe.', 'sangar-studio-readflow' ) );
        }

        // Verify post status and authorization
        if ( 'publish' !== $post->post_status ) {
            return new WP_Error( 'unauthorized', __( 'No tienes permisos para escuchar este post.', 'sangar-studio-readflow' ) );
        }

        // Verify post is not password protected
        if ( ! empty( $post->post_password ) ) {
            return new WP_Error( 'password_required', __( 'Este post está protegido por contraseña.', 'sangar-studio-readflow' ) );
        }

        $api_key = get_option( 'ssrf_api_key', '' );
        if ( empty( $api_key ) ) {
            return new WP_Error( 'missing_api_key', __( 'No se ha configurado la OpenAI API Key.', 'sangar-studio-readflow' ) );
        }

        $voice = get_option( 'ssrf_voice', 'alloy' );
        $model = get_option( 'ssrf_model', 'tts-1' );

        // Strip HTML, blocks, and clean up the post content
        $content = $post->post_content;
        
        // Remove shortcodes, tags and decode html entities
        $text = html_entity_decode( wp_strip_all_tags( strip_shortcodes( $content ) ), ENT_QUOTES, 'UTF-8' );
        
        // Clean line endings and multiple spaces
        $text = preg_replace( '/\s+/u', ' ', $text );
        $text = trim( $text );

        if ( empty( $text ) ) {
            return new WP_Error( 'empty_content', __( 'El post no contiene texto legible.', 'sangar-studio-readflow' ) );
        }

        // Fetch audio stream
        $audio_data = $this->fetch_openai_tts( $text, $api_key, $voice, $model );

        if ( is_wp_error( $audio_data ) ) {
            return $audio_data;
        }

        // Define local caching directory path and public URL
        $upload_dir = wp_upload_dir();
        $readflow_dir = $upload_dir['basedir'] . '/ssrf';
        $file_path  = $readflow_dir . '/post-' . $post_id . '.mp3';

        // Make sure caching folder exists
        if ( ! file_exists( $readflow_dir ) ) {
            wp_mkdir_p( $readflow_dir );
        }

        // Write binary MP3 data to disk
        $result = file_put_contents( $file_path, $audio_data );
        
        if ( $result === false ) {
            return new WP_Error( 'file_write_error', __( 'Error al escribir el archivo de audio en disco.', 'sangar-studio-readflow' ) );
        }

        return $upload_dir['baseurl'] . '/ssrf/post-' . $post_id . '.mp3';
    }

    /**
     * Clear the cached audio file for a single post.
     */
    public function clear_post_audio_cache( $post_id ) {
        $upload_dir = wp_upload_dir();
        $file_path  = $upload_dir['basedir'] . '/ssrf/post-' . $post_id . '.mp3';
        if ( file_exists( $file_path ) ) {
            wp_delete_file( $file_path );
        }
    }

    /**
     * Handle WP hook save_post: clear cache on edit and pre-generate audio if enabled.
     */
    public function handle_save_post( $post_id, $post, $update ) {
        // Bypass for autosaves, revisions, or non-post types
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check user editing capability
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Only clear and regenerate cache for standard posts (or customize to any post type)
        if ( ! is_object( $post ) || 'post' !== $post->post_type ) {
            return;
        }

        // Invalidate previous cache since content changed
        $this->clear_post_audio_cache( $post_id );

        // If automatic pre-generation is enabled and post is published
        $auto_generate = get_option( 'ssrf_auto_generate', false );
        $enable_ai     = get_option( 'ssrf_enable_ai', true );
        $api_key       = get_option( 'ssrf_api_key', '' );

        if ( $auto_generate && $enable_ai && ! empty( $api_key ) && 'publish' === $post->post_status ) {
            // Generate the audio so it is ready for the first reader
            $this->generate_audio_for_post( $post_id );
        }
    }

    /**
     * AJAX Action: Return existing cached audio URL, or generate and cache it.
     */
    public function ajax_get_audio() {
        check_ajax_referer( 'ssrf_frontend_nonce', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( __( 'ID de entrada inválido.', 'sangar-studio-readflow' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post || 'post' !== $post->post_type ) {
            wp_send_json_error( __( 'El post no existe.', 'sangar-studio-readflow' ) );
        }

        // Protect drafts, private posts, and password protected content
        if ( 'publish' !== $post->post_status ) {
            wp_send_json_error( __( 'No tienes permisos para escuchar este post.', 'sangar-studio-readflow' ) );
        }

        if ( ! empty( $post->post_password ) ) {
            wp_send_json_error( __( 'Este post está protegido por contraseña.', 'sangar-studio-readflow' ) );
        }

        $upload_dir = wp_upload_dir();
        $file_path  = $upload_dir['basedir'] . '/ssrf/post-' . $post_id . '.mp3';
        $file_url   = $upload_dir['baseurl'] . '/ssrf/post-' . $post_id . '.mp3';

        // 1. Check if the audio file has already been generated and is valid
        if ( file_exists( $file_path ) && filesize( $file_path ) > 0 ) {
            wp_send_json_success([
                'audio_url' => $file_url,
                'cached'    => true
            ]);
        }

        // 2. Prevent unauthenticated API abuse / guest-initiated on-demand generation if option is disabled
        $allow_guest_generation = get_option( 'ssrf_allow_guest_generation', false );
        if ( ! is_user_logged_in() && ! $allow_guest_generation ) {
            wp_send_json_error( __( 'La generación de audio bajo demanda para invitados está deshabilitada.', 'sangar-studio-readflow' ) );
        }

        // 3. Fallback to generating it if it doesn't exist
        $result = $this->generate_audio_for_post( $post_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success([
            'audio_url' => $result,
            'cached'    => false
        ]);
    }

    /**
     * Register Abilities category under WordPress 7.0 Abilities API
     */
    public function register_abilities_category() {
        if ( function_exists( 'wp_register_ability_category' ) ) {
            wp_register_ability_category( 'ssrf', [
                'label'       => __( 'Sangar Studio ReadFlow', 'sangar-studio-readflow' ),
                'description' => __( 'Abilities related to Text-to-Speech audio and reading calculations.', 'sangar-studio-readflow' ),
            ] );
        }
    }

    /**
     * Register Abilities under WordPress 7.0 Abilities API
     */
    public function register_abilities() {
        if ( function_exists( 'wp_register_ability' ) ) {
            wp_register_ability( 'ssrf/get-audio', [
                'label'               => __( 'Get or Generate Audio', 'sangar-studio-readflow' ),
                'description'         => __( 'Retrieves the cached MP3 URL of a post narration or triggers AI voice synthesis on-demand if no cache exists.', 'sangar-studio-readflow' ),
                'category'            => 'ssrf',
                'execute_callback'    => [ $this, 'ability_execute_get_audio' ],
                'permission_callback' => [ $this, 'ability_can_get_audio' ],
                'input_schema'        => [
                    'type'       => 'object',
                    'properties' => [
                        'post_id' => [
                            'type'        => 'integer',
                            'description' => __( 'The database ID of the published post.', 'sangar-studio-readflow' ),
                        ],
                    ],
                    'required'   => [ 'post_id' ],
                ],
                'meta'                => [
                    'show_in_rest' => true,
                ],
            ] );
        }
    }

    /**
     * Permission callback for WordPress 7.0 Abilities API get-audio
     */
    public function ability_can_get_audio( $input ) {
        $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
        if ( ! $post_id ) {
            return false;
        }

        $post = get_post( $post_id );
        if ( ! $post || 'post' !== $post->post_type ) {
            return false;
        }

        // Drafts/private posts are restricted, public is allowed
        if ( 'publish' !== $post->post_status ) {
            return current_user_can( 'edit_post', $post_id );
        }

        return true;
    }

    /**
     * Execution callback for WordPress 7.0 Abilities API get-audio
     */
    public function ability_execute_get_audio( $input ) {
        $post_id = intval( $input['post_id'] );

        $upload_dir = wp_upload_dir();
        $file_path  = $upload_dir['basedir'] . '/ssrf/post-' . $post_id . '.mp3';
        $file_url   = $upload_dir['baseurl'] . '/ssrf/post-' . $post_id . '.mp3';

        // Check cache first
        if ( file_exists( $file_path ) && filesize( $file_path ) > 0 ) {
            return [
                'success'   => true,
                'audio_url' => $file_url,
                'cached'    => true,
                'message'   => __( 'Audio recuperado con éxito de la caché.', 'sangar-studio-readflow' )
            ];
        }

        // Otherwise generate on the fly
        $result = $this->generate_audio_for_post( $post_id );

        if ( is_wp_error( $result ) ) {
            return [
                'success' => false,
                'message' => $result->get_error_message()
            ];
        }

        return [
            'success'   => true,
            'audio_url' => $result,
            'cached'    => false,
            'message'   => __( 'Audio generado y cacheado con éxito.', 'sangar-studio-readflow' )
        ];
    }
}
