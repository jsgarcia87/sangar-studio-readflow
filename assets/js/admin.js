/**
 * Readio - Administrative dashboard interactive script
 * Integrates WordPress color picker, range indicators, conditional visibility,
 * live API tester, and secure cache flushing actions.
 */

jQuery(document).ready(function($) {
    
    // -------------------------------------------------------------
    // 1. WORDPRESS COLOR PICKER INITIALIZATION
    // -------------------------------------------------------------
    if ($.isFunction($.fn.wpColorPicker)) {
        $('.readio-color-field').wpColorPicker();
    }

    // -------------------------------------------------------------
    // 2. DYNAMIC SLIDER INPUT VALUE FEEDBACK
    // -------------------------------------------------------------
    const wpmSlider = $('#readio_wpm');
    const wpmBadge  = $('#readio_wpm_val');

    if (wpmSlider.length && wpmBadge.length) {
        wpmSlider.on('input', function() {
            wpmBadge.text($(this).val() + ' PPM');
        });
    }

    // -------------------------------------------------------------
    // 3. API KEY VISIBILITY TOGGLE (PEEK ACTION)
    // -------------------------------------------------------------
    const togglePwBtn = $('#readio-toggle-pw-btn');
    const apiInput    = $('#readio_api_key_input');

    if (togglePwBtn.length && apiInput.length) {
        togglePwBtn.on('click', function() {
            const currentType = apiInput.attr('type');
            if (currentType === 'password') {
                apiInput.attr('type', 'text');
                togglePwBtn.text('🙈');
            } else {
                apiInput.attr('type', 'password');
                togglePwBtn.text('👁️');
            }
        });
    }

    // -------------------------------------------------------------
    // 4. CONDITIONAL ACCORDION FOR AI FIELDS
    // -------------------------------------------------------------
    const enableAiToggle = $('#readio_enable_ai');
    const aiFieldsGroup  = $('.readio-conditional-ai-fields');
    const testerBoxCard  = $('#readio-tester-box');

    if (enableAiToggle.length) {
        enableAiToggle.on('change', function() {
            if ($(this).is(':checked')) {
                aiFieldsGroup.slideDown(250);
                testerBoxCard.fadeIn(300);
            } else {
                aiFieldsGroup.slideUp(250);
                testerBoxCard.fadeOut(300);
            }
        });
    }

    // -------------------------------------------------------------
    // 5. LIVE CONNECTION TESTER (OPENAI SPEECH API)
    // -------------------------------------------------------------
    const testApiBtn       = $('#readio-btn-test-api');
    const testTextarea     = $('#readio-test-text');
    const testerStatus     = $('#readio-tester-status');
    const testerAudioWrap  = $('#readio-tester-audio-container');
    const testerAudioPlayer= $('#readio-tester-audio')[0];

    if (testApiBtn.length) {
        testApiBtn.on('click', function() {
            // Read parameters directly from active fields in real-time
            const currentApiKey = apiInput.val().trim();
            const voiceSelected = $('#readio_voice').val();
            const modelSelected = $('#readio_model').val();
            const textToSpeak   = testTextarea.val().trim();

            if (!currentApiKey) {
                testerStatus.removeClass('success loading').addClass('error').text('Introduce una API Key para realizar la prueba.');
                return;
            }

            if (!textToSpeak) {
                testerStatus.removeClass('success loading').addClass('error').text('Escribe algo de texto para sintetizar.');
                return;
            }

            // Display loading transition status
            testerStatus.removeClass('success error').addClass('loading').text(readio_admin.loading_text);
            testerAudioWrap.hide();
            testApiBtn.prop('disabled', true);

            // POST query
            $.ajax({
                url: readio_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'readio_test_api',
                    nonce: readio_admin.nonce,
                    api_key: currentApiKey,
                    text: textToSpeak,
                    voice: voiceSelected,
                    model: modelSelected
                },
                success: function(response) {
                    testApiBtn.prop('disabled', false);
                    if (response.success && response.data && response.data.audio_url) {
                        testerStatus.removeClass('error loading').addClass('success').text(readio_admin.test_success);
                        
                        // Feed generated mpeg string to player
                        testerAudioPlayer.src = response.data.audio_url;
                        testerAudioWrap.show();
                        testerAudioPlayer.play().catch(function(e) {
                            console.log("Autoplay blocked by browser. Click play manually.");
                        });
                    } else {
                        const errorMsg = response.data || readio_admin.test_fail;
                        testerStatus.removeClass('success loading').addClass('error').text(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    testApiBtn.prop('disabled', false);
                    testerStatus.removeClass('success loading').addClass('error').text(readio_admin.test_fail + " (" + error + ")");
                }
            });
        });
    }

    // -------------------------------------------------------------
    // 6. CACHE DELETION / MAINTENANCE HANDLER
    // -------------------------------------------------------------
    const clearCacheBtn = $('#readio-btn-clear-cache');
    const cacheCountText = $('#readio-cache-count');
    const cacheSizeText  = $('#readio-cache-size');

    if (clearCacheBtn.length) {
        clearCacheBtn.on('click', function() {
            if (!confirm('¿Estás seguro de que quieres eliminar todos los archivos de voz de IA almacenados localmente?')) {
                return;
            }

            clearCacheBtn.prop('disabled', true).text('Limpiando...');

            $.ajax({
                url: readio_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'readio_clear_cache',
                    nonce: readio_admin.nonce
                },
                success: function(response) {
                    clearCacheBtn.prop('disabled', false).html('🗑️ Limpiar Todos los Audios');
                    if (response.success) {
                        alert(response.data.message);
                        if (cacheCountText.length) cacheCountText.text(response.data.count);
                        if (cacheSizeText.length) cacheSizeText.text(response.data.size);
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    clearCacheBtn.prop('disabled', false).html('🗑️ Limpiar Todos los Audios');
                    alert('Fallo de red al limpiar la caché: ' + error);
                }
            });
        });
    }
});
