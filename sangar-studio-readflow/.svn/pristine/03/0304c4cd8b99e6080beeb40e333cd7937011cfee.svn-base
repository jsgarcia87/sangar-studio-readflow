<?php
/**
 * Sangar Studio ReadFlow Author Box Class
 * Manages WordPress user profile social links (LinkedIn, RRSS) and renders the premium Author Card at the end of posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SSRF_Author {

    public function __construct() {
        // User Profile Custom Fields (Edit profile in WP Admin)
        add_action( 'show_user_profile', [ $this, 'render_user_profile_fields' ] );
        add_action( 'edit_user_profile', [ $this, 'render_user_profile_fields' ] );
        add_action( 'personal_options_update', [ $this, 'save_user_profile_fields' ] );
        add_action( 'edit_user_profile_update', [ $this, 'save_user_profile_fields' ] );

        // Auto append Author Box to end of posts
        add_filter( 'the_content', [ $this, 'insert_author_box' ], 20 );

        // Shortcodes for manual insertion
        add_shortcode( 'ssrf_author', [ $this, 'render_shortcode' ] );
        add_shortcode( 'ssrf_author_box', [ $this, 'render_shortcode' ] );
    }

    /**
     * Display Author & Social profile fields in WordPress user profile edit screen.
     */
    public function render_user_profile_fields( $user ) {
        if ( ! current_user_can( 'read', $user->ID ) ) {
            return;
        }

        $tagline   = get_user_meta( $user->ID, '_ssrf_author_tagline', true );
        $avatar    = get_user_meta( $user->ID, '_ssrf_author_avatar', true );
        $linkedin  = get_user_meta( $user->ID, '_ssrf_author_linkedin', true );
        $twitter   = get_user_meta( $user->ID, '_ssrf_author_twitter', true );
        $instagram = get_user_meta( $user->ID, '_ssrf_author_instagram', true );
        $github    = get_user_meta( $user->ID, '_ssrf_author_github', true );
        $website   = get_user_meta( $user->ID, '_ssrf_author_website', true );
        ?>
        <h2><?php esc_html_e( 'Sangar Studio ReadFlow - Perfil de Autor & RRSS', 'sangar-studio-readflow' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Configura tu puesto profesional, foto personalizada y enlaces a LinkedIn y redes sociales que se mostrarán en la Caja de Autor al final de tus entradas.', 'sangar-studio-readflow' ); ?>
        </p>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="ssrf_author_tagline"><?php esc_html_e( 'Cargo / Tagline Profesional', 'sangar-studio-readflow' ); ?></label></th>
                <td>
                    <input type="text" name="ssrf_author_tagline" id="ssrf_author_tagline" value="<?php echo esc_attr( $tagline ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'ej. Especialista en IA & Redacción', 'sangar-studio-readflow' ); ?>" />
                    <p class="description"><?php esc_html_e( 'Breve título profesional que se muestra bajo tu nombre.', 'sangar-studio-readflow' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="ssrf_author_avatar"><?php esc_html_e( 'Foto de Autor Personalizada (URL)', 'sangar-studio-readflow' ); ?></label></th>
                <td>
                    <input type="url" name="ssrf_author_avatar" id="ssrf_author_avatar" value="<?php echo esc_url( $avatar ); ?>" class="regular-text" placeholder="https://" />
                    <p class="description"><?php esc_html_e( 'Opcional. Introduce la URL de una imagen para sobrescribir tu Gravatar por defecto en la caja de autor.', 'sangar-studio-readflow' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="ssrf_author_linkedin"><?php esc_html_e( 'LinkedIn URL', 'sangar-studio-readflow' ); ?></label></th>
                <td>
                    <input type="url" name="ssrf_author_linkedin" id="ssrf_author_linkedin" value="<?php echo esc_url( $linkedin ); ?>" class="regular-text" placeholder="https://linkedin.com/in/tu-perfil" />
                </td>
            </tr>
            <tr>
                <th><label for="ssrf_author_twitter"><?php esc_html_e( 'X (Twitter) URL', 'sangar-studio-readflow' ); ?></label></th>
                <td>
                    <input type="url" name="ssrf_author_twitter" id="ssrf_author_twitter" value="<?php echo esc_url( $twitter ); ?>" class="regular-text" placeholder="https://x.com/tu-usuario" />
                </td>
            </tr>
            <tr>
                <th><label for="ssrf_author_instagram"><?php esc_html_e( 'Instagram URL', 'sangar-studio-readflow' ); ?></label></th>
                <td>
                    <input type="url" name="ssrf_author_instagram" id="ssrf_author_instagram" value="<?php echo esc_url( $instagram ); ?>" class="regular-text" placeholder="https://instagram.com/tu-usuario" />
                </td>
            </tr>
            <tr>
                <th><label for="ssrf_author_github"><?php esc_html_e( 'GitHub URL', 'sangar-studio-readflow' ); ?></label></th>
                <td>
                    <input type="url" name="ssrf_author_github" id="ssrf_author_github" value="<?php echo esc_url( $github ); ?>" class="regular-text" placeholder="https://github.com/tu-usuario" />
                </td>
            </tr>
            <tr>
                <th><label for="ssrf_author_website"><?php esc_html_e( 'Sitio Web / Portfolio', 'sangar-studio-readflow' ); ?></label></th>
                <td>
                    <input type="url" name="ssrf_author_website" id="ssrf_author_website" value="<?php echo esc_url( $website ); ?>" class="regular-text" placeholder="https://tuporfolio.com" />
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save user profile fields.
     */
    public function save_user_profile_fields( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        $fields = [
            '_ssrf_author_tagline'   => 'sanitize_text_field',
            '_ssrf_author_avatar'    => 'esc_url_raw',
            '_ssrf_author_linkedin'  => 'esc_url_raw',
            '_ssrf_author_twitter'   => 'esc_url_raw',
            '_ssrf_author_instagram' => 'esc_url_raw',
            '_ssrf_author_github'    => 'esc_url_raw',
            '_ssrf_author_website'   => 'esc_url_raw',
        ];

        foreach ( $fields as $meta_key => $sanitizer ) {
            $input_name = substr( $meta_key, 1 ); // remove leading underscore
            if ( isset( $_POST[ $input_name ] ) ) {
                $sanitized_value = call_user_func( $sanitizer, wp_unslash( $_POST[ $input_name ] ) );
                update_user_meta( $user_id, $meta_key, $sanitized_value );
            }
        }
    }

    /**
     * Automatically insert author box at the end of single post content.
     */
    public function insert_author_box( $content ) {
        if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        if ( 'post' !== get_post_type() ) {
            return $content;
        }

        $enable_author_box = get_option( 'ssrf_enable_author_box', true );
        if ( ! $enable_author_box ) {
            return $content;
        }

        $author_box = $this->render_author_box();
        return $content . $author_box;
    }

    /**
     * Shortcode handler.
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( [
            'id' => null,
        ], $atts, 'ssrf_author' );

        $author_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : null;
        return $this->render_author_box( $author_id );
    }

    /**
     * Build and render the premium Author Box HTML markup.
     */
    public function render_author_box( $author_id = null ) {
        if ( ! $author_id ) {
            $post = get_post();
            if ( ! $post ) {
                return '';
            }
            $author_id = $post->post_author;
        }

        $user = get_userdata( $author_id );
        if ( ! $user ) {
            return '';
        }

        $style        = get_option( 'ssrf_author_box_style', 'glass' );
        $title        = get_option( 'ssrf_author_box_title', __( 'Escrito por', 'sangar-studio-readflow' ) );
        $avatar_style = get_option( 'ssrf_author_avatar_style', 'circle' );
        $social_style = get_option( 'ssrf_author_social_style', 'icons' );
        $fallback_bio = get_option( 'ssrf_author_fallback_bio', '' );

        $display_name = $user->display_name;
        $author_url   = get_author_posts_url( $author_id );
        
        $tagline   = get_user_meta( $author_id, '_ssrf_author_tagline', true );
        $avatar_override = get_user_meta( $author_id, '_ssrf_author_avatar', true );
        $linkedin  = get_user_meta( $author_id, '_ssrf_author_linkedin', true );
        $twitter   = get_user_meta( $author_id, '_ssrf_author_twitter', true );
        $instagram = get_user_meta( $author_id, '_ssrf_author_instagram', true );
        $github    = get_user_meta( $author_id, '_ssrf_author_github', true );
        $website   = get_user_meta( $author_id, '_ssrf_author_website', true );
        if ( empty( $website ) && ! empty( $user->user_url ) ) {
            $website = $user->user_url;
        }

        // Avatar URL
        if ( ! empty( $avatar_override ) && filter_var( $avatar_override, FILTER_VALIDATE_URL ) ) {
            $avatar_url = $avatar_override;
        } else {
            $avatar_url = get_avatar_url( $author_id, [ 'size' => 180 ] );
        }

        // Biography
        $bio = get_the_author_meta( 'description', $author_id );
        if ( empty( trim( $bio ) ) && ! empty( $fallback_bio ) ) {
            $bio = $fallback_bio;
        }

        // Build SVG icons
        $svg_linkedin = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 3A2 2 0 0 1 21 5V19A2 2 0 0 1 19 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H19M18.5 18.5V13.2A3.26 3.26 0 0 0 15.24 9.94C14.39 9.94 13.4 10.46 12.92 11.24V10.13H10.13V18.5H12.92V13.57C12.92 12.8 13.54 12.17 14.31 12.17A1.4 1.4 0 0 1 15.71 13.57V18.5H18.5M6.88 8.56A1.68 1.68 0 0 0 8.56 6.88C8.56 5.95 7.81 5.19 6.88 5.19A1.69 1.69 0 0 0 5.19 6.88C5.19 7.81 5.95 8.56 6.88 8.56M8.27 18.5V10.13H5.5V18.5H8.27Z"/></svg>';
        
        $svg_twitter = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
        
        $svg_instagram = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7.8 2H16.2C19.4 2 22 4.6 22 7.8V16.2A5.8 5.8 0 0 1 16.2 22H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2M7.6 4A3.6 3.6 0 0 0 4 7.6V16.4C4 18.39 5.61 20 7.6 20H16.4A3.6 3.6 0 0 0 20 16.4V7.6C20 5.61 18.39 4 16.4 4H7.6M17.25 5.5A1.25 1.25 0 0 1 18.5 6.75A1.25 1.25 0 0 1 17.25 8A1.25 1.25 0 0 1 16 6.75A1.25 1.25 0 0 1 17.25 5.5M12 7A5 5 0 0 1 17 12A5 5 0 0 1 12 17A5 5 0 0 1 7 12A5 5 0 0 1 12 7M12 9A3 3 0 0 0 9 12A3 3 0 0 0 12 15A3 3 0 0 0 15 12A3 3 0 0 0 12 9Z"/></svg>';
        
        $svg_github = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2A10 10 0 0 0 2 12C2 16.42 4.87 20.17 8.84 21.5C9.34 21.58 9.5 21.27 9.5 21C9.5 20.77 9.5 20.14 9.5 19.31C6.73 19.91 6.14 17.97 6.14 17.97C5.68 16.81 5.03 16.5 5.03 16.5C4.12 15.88 5.1 15.9 5.1 15.9C6.1 15.97 6.63 16.93 6.63 16.93C7.5 18.45 8.97 18 9.54 17.76C9.63 17.11 9.89 16.67 10.17 16.42C7.95 16.17 5.62 15.31 5.62 11.5C5.62 10.39 6 9.5 6.65 8.79C6.55 8.54 6.2 7.5 6.75 6.15C6.75 6.15 7.59 5.88 9.5 7.17C10.29 6.95 11.15 6.84 12 6.84C12.85 6.84 13.71 6.95 14.5 7.17C16.41 5.88 17.25 6.15 17.25 6.15C17.8 7.5 17.45 8.54 17.35 8.79C18 9.5 18.38 10.39 18.38 11.5C18.38 15.32 16.04 16.16 13.81 16.41C14.17 16.72 14.5 17.33 14.5 18.26C14.5 19.6 14.5 20.68 14.5 21C14.5 21.27 14.66 21.59 15.17 21.5C19.14 20.16 22 16.42 22 12A10 10 0 0 0 12 2Z"/></svg>';
        
        $svg_website = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';

        $container_class = 'ssrf-author-box ssrf-author-theme-' . esc_attr( $style ) . ' ssrf-avatar-' . esc_attr( $avatar_style ) . ' ssrf-social-' . esc_attr( $social_style );

        ob_start();
        ?>
        <div class="<?php echo esc_attr( $container_class ); ?>">
            <?php if ( ! empty( $title ) ) : ?>
                <div class="ssrf-author-header">
                    <span class="ssrf-author-badge"><?php echo esc_html( $title ); ?></span>
                </div>
            <?php endif; ?>

            <div class="ssrf-author-body">
                <div class="ssrf-author-avatar-wrap">
                    <a href="<?php echo esc_url( $author_url ); ?>" class="ssrf-avatar-link" aria-label="<?php echo esc_attr( $display_name ); ?>">
                        <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" class="ssrf-author-avatar" width="96" height="96" loading="lazy" />
                        <span class="ssrf-avatar-glow"></span>
                    </a>
                </div>

                <div class="ssrf-author-info">
                    <div class="ssrf-author-title-row">
                        <h3 class="ssrf-author-name">
                            <a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $display_name ); ?></a>
                        </h3>
                        <?php if ( ! empty( $tagline ) ) : ?>
                            <span class="ssrf-author-tagline"><?php echo esc_html( $tagline ); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ( ! empty( $bio ) ) : ?>
                        <div class="ssrf-author-bio">
                            <?php echo wp_kses_post( wpautop( $bio ) ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $linkedin ) || ! empty( $twitter ) || ! empty( $instagram ) || ! empty( $github ) || ! empty( $website ) ) : ?>
                        <div class="ssrf-author-socials">
                            <?php if ( ! empty( $linkedin ) ) : ?>
                                <a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer" class="ssrf-social-link ssrf-linkedin" title="LinkedIn" aria-label="LinkedIn">
                                    <?php echo $svg_linkedin; ?>
                                    <?php if ( 'buttons' === $social_style ) : ?><span class="ssrf-social-text">LinkedIn</span><?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ( ! empty( $twitter ) ) : ?>
                                <a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer" class="ssrf-social-link ssrf-twitter" title="X (Twitter)" aria-label="X (Twitter)">
                                    <?php echo $svg_twitter; ?>
                                    <?php if ( 'buttons' === $social_style ) : ?><span class="ssrf-social-text">X</span><?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ( ! empty( $instagram ) ) : ?>
                                <a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer" class="ssrf-social-link ssrf-instagram" title="Instagram" aria-label="Instagram">
                                    <?php echo $svg_instagram; ?>
                                    <?php if ( 'buttons' === $social_style ) : ?><span class="ssrf-social-text">Instagram</span><?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ( ! empty( $github ) ) : ?>
                                <a href="<?php echo esc_url( $github ); ?>" target="_blank" rel="noopener noreferrer" class="ssrf-social-link ssrf-github" title="GitHub" aria-label="GitHub">
                                    <?php echo $svg_github; ?>
                                    <?php if ( 'buttons' === $social_style ) : ?><span class="ssrf-social-text">GitHub</span><?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ( ! empty( $website ) ) : ?>
                                <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="ssrf-social-link ssrf-website" title="<?php esc_attr_e( 'Sitio Web', 'sangar-studio-readflow' ); ?>" aria-label="<?php esc_attr_e( 'Sitio Web', 'sangar-studio-readflow' ); ?>">
                                    <?php echo $svg_website; ?>
                                    <?php if ( 'buttons' === $social_style ) : ?><span class="ssrf-social-text"><?php esc_html_e( 'Web', 'sangar-studio-readflow' ); ?></span><?php endif; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
