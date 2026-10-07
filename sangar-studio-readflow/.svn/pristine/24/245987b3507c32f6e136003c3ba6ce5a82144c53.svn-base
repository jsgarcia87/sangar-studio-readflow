=== Sangar Studio ReadFlow ===
Contributors: Sangar Studio
Tags: reading-time, text-to-speech, audio-player, ai-voice, accessibility
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays blog post reading times and generates premium AI voice audio narrations using OpenAI TTS with local caching.

== Description ==

**Sangar Studio ReadFlow** is a premium, high-fidelity WordPress plugin designed to elevate the user reading and auditory experience on your website. It calculates the reading time of your posts based on custom speed parameters and inserts a gorgeous, glassmorphic player widget that lets users listen to posts.

By integrating directly with **OpenAI's state-of-the-art TTS models**, **Sangar Studio ReadFlow** converts your blog content into natural, fluid, human-like voice recordings. It handles OpenAI's character limitations gracefully by dividing long essays into sentence-aware blocks and stitching the audio binaries together seamlessly.

To protect your budget and increase speed, **Sangar Studio ReadFlow features a robust local caching system**. It generates the voice file only once per post and caches it directly inside your server's uploads directory. Every subsequent listener loads the audio instantly in a custom-designed web player.

If no API Key is provided, or if the API suffers a network failure, the plugin automatically falls back to the **Web Speech API SpeechSynthesis**, providing free, zero-config native voice synthesis directly inside the reader's browser.

=== Key Features ===
*   **Accurate Reading Time Indicator:** Computes reading times based on custom Words Per Minute (WPM) settings.
*   **Premium Custom HTML5 Player:** Ditch the default browser audio bar. Features stylized play/pause buttons, loading spinners, sound-wave animations, and draggable seek sliders.
*   **OpenAI TTS Integration:** Access six natural voices (Alloy, Echo, Fable, Onyx, Nova, Shimmer) with standard `tts-1` or high-definition `tts-1-hd` qualities.
*   **Unlimited Text Length Chunking:** Sentence-intelligent text-splitting bypasses API size caps for infinite post narration.
*   **Smart Local Caching:** Saves generated MP3s locally. Zero repetitive API billing overhead for repeat readers.
*   **Auto Pre-Generation:** Optional setting pre-renders post narration in the background as soon as you hit "Publish".
*   **Sleek Administration Panel:** Manage credentials, customize accent colors, adjust reading formulas, check cache sizes, test voices with live playback, and flush files with one click.
*   **Zero-Config Web Speech Fallback:** Universal native voice narration fallback if AI models are disabled or run out of quota.
*   **Adjustable Playback Velocity:** Speed options (`0.8x`, `1.0x`, `1.2x`, `1.5x`, `2.0x`) for listeners on the go.
*   **Direct MP3 Download Link:** Give readers the option to take post narrations with them.

== Installation ==

1. Upload the `sangar-studio-readflow` folder to the `/wp-content/plugins/` directory, or upload the ZIP file via **Plugins > Add New > Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Settings > ReadFlow 🎙️** in your admin dashboard.
4. (Optional) Toggle "Activar Voz por IA", input your OpenAI API Key, and select your default voice and model.
5. Customize your visual preferences, reading speed WPM, and Author Box options.
6. The reading time and AI audio player will appear automatically on your blog posts!

== Frequently Asked Questions ==

= Does this plugin work without an OpenAI API Key? =
Yes! Without an API Key, Sangar Studio ReadFlow automatically switches to the Web Speech API fallback, synthesizing text-to-speech using the reader's native browser voice.

= How does local MP3 caching work? =
When an article is listened to for the first time (or pre-generated upon publishing), the audio file is stored in `wp-content/uploads/sangar-studio-readflow/`. All future visitors listen directly to this local file with 0 ms generation latency and zero API cost.

= How is cache invalidated when I edit a post? =
The plugin listens to post updates. Saving, editing, or trashing a post automatically deletes its corresponding cached MP3 so that the fresh content is compiled on the next listener's play click.

= Does the text chunking support very long articles? =
Yes! Sangar Studio ReadFlow breaks articles into smart, semantic chunks of approximately 3,500 characters, avoiding breaking mid-word or mid-sentence. It submits each chunk sequentially and compiles the resulting binaries into a unified audio stream, bypassing OpenAI's 4,096 character limit easily.

= How can I place the widget or Author Box manually? =
Select "Insertar manualmente" in the settings, and insert the shortcode `[sangar-studio-readflow]` for the audio player, and `[ssrf_author]` (or `[ssrf_author_box]`) for the Author Box anywhere in your post text or theme files:
`<?php echo do_shortcode('[sangar-studio-readflow]'); ?>`

(The legacy shortcode `[readio]` is also fully supported for backward compatibility).

= Can I customize the Author Box social icons and avatar shape? =
Yes! From **Settings > ReadFlow 🎙️**, you can choose circle, rounded, or hexagon avatar styles with a hover glow effect, and toggle between SVG icon buttons or branded text links for LinkedIn, X, Instagram, GitHub, and Website.

== Screenshots ==

1. Premium Compact Bar audio widget with live equalizer animation and reading time statistics.
2. Responsive Author Box with custom social network icons and avatar styling.
3. Modern WordPress Admin settings dashboard with Live Voice Tester and API configuration.

== Changelog ==

= 1.3.0 =
* [Feature] Added an integrated, responsive Author Box (Caja de Autor) module (`SSRF_Author`) displayed automatically at the bottom of blog posts or via shortcode `[ssrf_author]` / `[ssrf_author_box]`.
* [Feature] Added custom user profile meta fields in WordPress Admin (**Users > Profile**) for LinkedIn, X (Twitter), Instagram, GitHub, Website, and Professional Tagline.
* [Feature] Added customizable Author Box visual themes (Modern Glassmorphic, Minimalist, Card), avatar shapes (Circle, Rounded, Hexagon with glow effect), and SVG social button styles.
* [Feature] Redesigned the audio player frontend with a sleek "Compact Bar" layout mode (Spotify / Apple Music inspired) that eliminates bulky nested block spacing and padding.
* [Feature] Added "Minimal" and "Card" layout options for the audio widget to suit different theme aesthetics.
* [Feature] Added granular widget customization settings: control visible statistics (time + words, time only, words only, or hidden), player max-width, drop-shadow styles (Subtle, Medium, Neon accent, None), and mode indicator badge toggle.
* [Improvement] Removed hardcoded inline styles from frontend player templates in favor of CSS variables and responsive classes for cleaner markup and improved accessibility.
* [Improvement] Updated administrative Settings page UI with intuitive cards and dedicated Author Box configuration controls.
* [Review] Full syntax and WordPress coding standards verification passed.

= 1.2.1 =
* Fixed Text Domain Mismatch errors for proper i18n compatibility.

= 1.2.0 =
* Added full WordPress 7.0 compatibility.
* Integrated natively with the WordPress 7.0 Abilities API (safely supported on WP 6.9+), exposing post audio narration generation and retrieval as standard system abilities.
* Ready for the WordPress 7.0 Native AI Client and Model Context Protocol (MCP) tool-calling support.
* Fully verified compatibility with PHP 8.3 and PHP 8.4 recommended guidelines for WordPress 7.0.

= 1.1.1 =
* Fixed all WordPress Plugin Check tool errors and warnings.
* Replaced strip_tags with wp_strip_all_tags for better compliance.
* Added missing translators comments to translatable strings.
* Scoped uninstall variables and migrated to WP_Filesystem API in uninstall.php.
* Cleaned up deprecated load_plugin_textdomain call.

= 1.1.0 =
* Added OpenAI TTS integration with 6 beautiful natural voices.
* Added smart, sentence-aware chunking for infinite-length blog post compilation.
* Added server-level binary caching (`wp-content/uploads/sangar-studio-readflow/`).
* Added automatic background audio compilation on post publish.
* Added custom HTML5 player visual overlay with progress tracking, visual equalizer animations, speed adjustment dropdown, and direct MP3 downloads.
* Added Live API Connection tester in administration dashboard.
* Fully refactored classes, prefixing, variables, and options names to be 100% compliant with WordPress.org submission requirements.

= 1.0.0 =
* Initial structure. Basic reading time calculations and raw browser SpeechSynthesis triggers.

== Privacy & Third-Party Service Disclosure ==

This plugin integrates with and sends requests to a third-party service: **OpenAI API** (specifically the Text-to-Speech service).

* **What is sent:** Only the plain text content of your published posts is transmitted to OpenAI's servers to synthesize the voice.
* **What is NOT sent:** No reader IP addresses, personal data, browser details, or cookies are sent to OpenAI.
* **Data Processing & Storage:** The audio generation is executed server-side. The compiled MP3 is saved locally on your self-hosted WordPress site inside `/wp-content/uploads/sangar-studio-readflow/`. No external CDN or tracking tags are embedded in the reader's browser.
* **OpenAI Terms:** For more details on data retention policies, please check the OpenAI Privacy Policy: https://openai.com/policies/privacy-policy
