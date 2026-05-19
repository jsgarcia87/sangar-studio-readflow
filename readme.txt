=== Sangar Studio ReadFlow ===
Contributors: antigravity
Tags: reading-time, text-to-speech, audio-player, ai-voice, openai-tts, speech-synthesis, accessibility
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A premium WordPress plugin that calculates post reading times, displays a sleek progress widget, and generates high-fidelity AI audio versions using OpenAI's Text-to-Speech API, with local caching and browser SpeechSynthesis fallback.

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
5. Customize visual configurations (accent colors, positioning, reading speeds) and click **Guardar Configuración**.

== Frequently Asked Questions ==

= Is an OpenAI API Key required? =
No. If you leave "Voz por IA" disabled or do not provide an API Key, the plugin operates in fallback mode, using the reader's local web browser engine (`window.speechSynthesis`) to synthesize voice completely free and instantly.

= Where are the generated audio files saved? =
Files are saved inside the `/wp-content/uploads/sangar-studio-readflow/` directory as static MP3 files. They are automatically named according to post IDs (e.g. `post-42.mp3`).

= How is cache invalidated when I edit a post? =
The plugin listens to post updates. Saving, editing, or trashing a post automatically deletes its corresponding cached MP3 so that the fresh content is compiled on the next listener's play click.

= Does the text chunking support very long articles? =
Yes! Sangar Studio ReadFlow breaks articles into smart, semantic chunks of approximately 3,500 characters, avoiding breaking mid-word or mid-sentence. It submits each chunk sequentially and compiles the resulting binaries into a unified audio stream, bypassing OpenAI's 4,096 character limit easily.

= How can I place the widget manually? =
Select "Insertar manualmente" in the settings, and insert the shortcode `[sangar-studio-readflow]` anywhere in your post text, or call it programmatically in your theme files:
`<?php echo do_shortcode('[sangar-studio-readflow]'); ?>`

(The legacy shortcode `[readio]` is also fully supported for backward compatibility).

== Changelog ==

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
