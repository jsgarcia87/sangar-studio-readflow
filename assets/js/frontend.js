/**
 * Readio - Frontend Media Player logic
 * Manages the custom audio player, AJAX generation, and browser SpeechSynthesis fallback.
 */

document.addEventListener('DOMContentLoaded', function() {
    const widget = document.getElementById('readio-widget-box');
    if (!widget) return;

    const postId          = widget.getAttribute('data-post-id');
    const playBtn         = document.getElementById('readio-play-btn');
    const playLabel       = document.getElementById('readio-play-label');
    const playIcon        = playBtn.querySelector('.readio-svg-play');
    const pauseIcon       = playBtn.querySelector('.readio-svg-pause');
    const spinnerIcon     = playBtn.querySelector('.readio-svg-spinner');
    const waveAnim        = document.getElementById('readio-wave-animation');
    
    // Custom Player Elements
    const timeline        = document.getElementById('readio-timeline');
    const progressRail    = document.getElementById('readio-progress-rail');
    const progressFill    = document.getElementById('readio-progress-fill');
    const progressKnob    = document.getElementById('readio-progress-knob');
    const currentTimeText = document.getElementById('readio-current-time');
    const durationText    = document.getElementById('readio-total-duration');
    
    // Controls Footer
    const footerControls  = document.getElementById('readio-footer-controls');
    const speedToggleBtn  = document.getElementById('readio-speed-toggle-btn');
    const speedDropdown   = document.getElementById('readio-speed-dropdown');
    const currentSpeedText= document.getElementById('readio-current-speed');
    const downloadBtn     = document.getElementById('readio-btn-download');
    const modeLabel       = document.getElementById('readio-mode-label');
    
    // HTML5 Audio Tag
    const audioPlayer     = document.getElementById('readio-html5-audio');

    // State Variables
    let isAiMode          = readio_obj.has_ai === '1' || readio_obj.has_ai === true;
    let isSpeechActive    = false; // For native synthesis
    let currentUtterance  = null;
    let playbackSpeed     = 1.0;
    let audioLoaded       = false;

    // -------------------------------------------------------------
    // 1. HELPERS & UTILITIES
    // -------------------------------------------------------------

    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function showButtonState(state) {
        // Reset icons
        playIcon.style.display    = 'none';
        pauseIcon.style.display   = 'none';
        spinnerIcon.style.display = 'none';

        if (state === 'play') {
            playIcon.style.display = 'block';
        } else if (state === 'pause') {
            pauseIcon.style.display = 'block';
        } else if (state === 'loading') {
            spinnerIcon.style.display = 'block';
        }
    }

    // -------------------------------------------------------------
    // 2. TIMELINE SEEK & PROGRESS HANDLERS
    // -------------------------------------------------------------

    function updateProgressUI(percent) {
        const p = Math.max(0, Math.min(100, percent));
        progressFill.style.width = p + '%';
        progressKnob.style.left  = p + '%';
    }

    // Handle Timeline Clicking
    progressRail.addEventListener('click', function(e) {
        if (!audioLoaded || !isAiMode) return;
        const rect = progressRail.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        
        if (audioPlayer.duration) {
            audioPlayer.currentTime = percent * audioPlayer.duration;
            updateProgressUI(percent * 100);
        }
    });

    // Handle Time Progress Updates
    audioPlayer.addEventListener('timeupdate', function() {
        if (!audioPlayer.duration) return;
        const percent = (audioPlayer.currentTime / audioPlayer.duration) * 100;
        updateProgressUI(percent);
        currentTimeText.innerText = formatTime(audioPlayer.currentTime);
    });

    audioPlayer.addEventListener('loadedmetadata', function() {
        durationText.innerText = formatTime(audioPlayer.duration);
    });

    audioPlayer.addEventListener('durationchange', function() {
        durationText.innerText = formatTime(audioPlayer.duration);
    });

    // -------------------------------------------------------------
    // 3. SOUND REPRODUCTION (AI MODE)
    // -------------------------------------------------------------

    function playAiAudio() {
        if (audioLoaded) {
            // Already loaded, toggle play
            if (audioPlayer.paused) {
                audioPlayer.play();
            } else {
                audioPlayer.pause();
            }
            return;
        }

        // Initialize AJAX compilation
        showButtonState('loading');
        playLabel.innerText = readio_obj.text.generating;
        playBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'readio_get_audio');
        formData.append('post_id', postId);
        formData.append('nonce', readio_obj.nonce);

        fetch(readio_obj.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            playBtn.disabled = false;
            
            if (data.success && data.data && data.data.audio_url) {
                const url = data.data.audio_url;
                
                audioPlayer.src = url;
                audioPlayer.playbackRate = playbackSpeed;
                audioPlayer.load();
                
                // Show player details
                timeline.style.display       = 'flex';
                footerControls.style.display = 'flex';
                if (downloadBtn) {
                    downloadBtn.href = url;
                }

                playLabel.innerText = readio_obj.text.playing_ai;
                audioLoaded = true;

                // Auto-start play
                audioPlayer.play()
                    .catch(e => {
                        console.warn("Auto-play block prevent:", e);
                        showButtonState('play');
                        playLabel.innerText = readio_obj.text.play;
                    });
            } else {
                console.error("OpenAI failed, falling back:", data.data);
                // Trigger graceful fallback to synthesis
                triggerFallback();
            }
        })
        .catch(error => {
            console.error("AJAX Error, falling back:", error);
            playBtn.disabled = false;
            triggerFallback();
        });
    }

    function triggerFallback() {
        isAiMode = false;
        modeLabel.innerText = readio_obj.text.playing_nat;
        const iconDot = footerControls.querySelector('.readio-indicator-dot');
        if (iconDot) iconDot.style.background = '#eab308'; // Amber for fallback
        
        // Hide elements not supported by synthesis
        timeline.style.display = 'none';
        if (downloadBtn) downloadBtn.style.display = 'none';
        
        // Show controls footer (for speed)
        footerControls.style.display = 'flex';

        playLocalSpeech();
    }

    // Audio Player State Hooks
    audioPlayer.addEventListener('play', function() {
        showButtonState('pause');
        waveAnim.classList.add('playing');
        playLabel.innerText = readio_obj.text.playing_ai;
    });

    audioPlayer.addEventListener('pause', function() {
        showButtonState('play');
        waveAnim.classList.remove('playing');
        playLabel.innerText = readio_obj.text.play;
    });

    audioPlayer.addEventListener('ended', function() {
        showButtonState('play');
        waveAnim.classList.remove('playing');
        playLabel.innerText = readio_obj.text.play;
        updateProgressUI(0);
        currentTimeText.innerText = '0:00';
    });

    audioPlayer.addEventListener('error', function() {
        showButtonState('play');
        waveAnim.classList.remove('playing');
        playLabel.innerText = readio_obj.text.error;
    });

    // -------------------------------------------------------------
    // 4. SPEECH SYNTHESIS (LOCAL MODE)
    // -------------------------------------------------------------

    function playLocalSpeech() {
        if ('speechSynthesis' in window) {
            if (isSpeechActive) {
                if (window.speechSynthesis.speaking) {
                    if (window.speechSynthesis.paused) {
                        window.speechSynthesis.resume();
                        isSpeechActive = true;
                        showButtonState('pause');
                        waveAnim.classList.add('playing');
                        playLabel.innerText = readio_obj.text.playing_nat;
                    } else {
                        window.speechSynthesis.pause();
                        showButtonState('play');
                        waveAnim.classList.remove('playing');
                        playLabel.innerText = readio_obj.text.play;
                    }
                }
                return;
            }

            // Cancel other plays
            window.speechSynthesis.cancel();

            // Extract entry content dynamically
            const selectors = ['.entry-content', '.post-content', '.post-entry', 'article', 'main'];
            let contentText = '';
            
            for (const selector of selectors) {
                const element = document.querySelector(selector);
                if (element) {
                    // Extract text but ignore our widget container itself
                    const cloned = element.cloneNode(true);
                    const widgetInner = cloned.querySelector('.readio-widget');
                    if (widgetInner) widgetInner.remove();
                    
                    contentText = cloned.innerText;
                    break;
                }
            }

            if (!contentText) {
                // If template container class was unfindable, search paragraphs
                const paragraphs = document.querySelectorAll('p');
                const pTexts = [];
                paragraphs.forEach(p => {
                    if (!p.closest('.readio-widget')) {
                        pTexts.push(p.innerText);
                    }
                });
                contentText = pTexts.join(' ');
            }

            if (!contentText) {
                alert("No se pudo detectar el texto del post para reproducir.");
                return;
            }

            // Slice out excess content for performance safety if needed
            contentText = contentText.substring(0, 10000);

            currentUtterance = new SpeechSynthesisUtterance(contentText);
            
            // Detect Page / WP locale
            let localeCode = readio_obj.locale || document.documentElement.lang || 'es-ES';
            // standard sanitization for synthesis format (usually language code hyphen country code)
            localeCode = localeCode.replace('_', '-');
            currentUtterance.lang = localeCode;
            currentUtterance.rate = playbackSpeed;

            // Events
            currentUtterance.onstart = function() {
                isSpeechActive = true;
                showButtonState('pause');
                waveAnim.classList.add('playing');
                playLabel.innerText = readio_obj.text.playing_nat;
                footerControls.style.display = 'flex';
            };

            currentUtterance.onend = function() {
                isSpeechActive = false;
                showButtonState('play');
                waveAnim.classList.remove('playing');
                playLabel.innerText = readio_obj.text.play;
            };

            currentUtterance.onerror = function(event) {
                console.error("Speech Synthesis Error:", event);
                isSpeechActive = false;
                showButtonState('play');
                waveAnim.classList.remove('playing');
                playLabel.innerText = readio_obj.text.play;
            };

            window.speechSynthesis.speak(currentUtterance);
        } else {
            alert("Tu navegador no soporta síntesis de voz nativa.");
        }
    }

    // -------------------------------------------------------------
    // 5. CORE PLAY TRIGGER CONTROL
    // -------------------------------------------------------------

    playBtn.addEventListener('click', function() {
        if (isAiMode) {
            playAiAudio();
        } else {
            playLocalSpeech();
        }
    });

    // -------------------------------------------------------------
    // 6. AUDIO VELOCITY SPEED DROPDOWN CONTROLLER
    // -------------------------------------------------------------

    speedToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const disp = window.getComputedStyle(speedDropdown).display;
        speedDropdown.style.display = (disp === 'none') ? 'flex' : 'none';
    });

    speedDropdown.querySelectorAll('li').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const speed = parseFloat(this.getAttribute('data-speed'));
            playbackSpeed = speed;
            currentSpeedText.innerText = speed + 'x';
            
            // Clear active styles
            speedDropdown.querySelectorAll('li').forEach(li => li.classList.remove('active'));
            this.classList.add('active');
            
            // Set speed parameters based on mode
            if (isAiMode && audioLoaded) {
                audioPlayer.playbackRate = speed;
            } else if (!isAiMode) {
                // Changing speed during synthesis requires a re-start to register
                if (isSpeechActive) {
                    window.speechSynthesis.cancel();
                    isSpeechActive = false;
                    setTimeout(playLocalSpeech, 100);
                }
            }

            speedDropdown.style.display = 'none';
        });
    });

    // Global Close drop list
    document.addEventListener('click', function() {
        speedDropdown.style.display = 'none';
    });
});
