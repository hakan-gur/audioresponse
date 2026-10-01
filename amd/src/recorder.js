define([], function() {
    'use strict';

    /**
     * @module qtype_audioresponse/recorder
     */
    
    const CHECK_INTERVAL = 100;
    
    function checkCanWork() {
        if (!(navigator.mediaDevices && window.MediaRecorder)) {
            return false;
        }
        return location.protocol === 'https:' ||
            location.host === 'localhost' ||
            location.host === '127.0.0.1';
    }
    
    function playStartSignal() {
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) {
                return;
            }
            const context = new AudioContextClass();
            const oscillator = context.createOscillator();
            const gain = context.createGain();
            oscillator.frequency.value = 880;
            gain.gain.value = 0.12;
            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.18);
            oscillator.onended = () => context.close();
        } catch (e) {
            // A blocked audio context must not prevent recording.
        }
    }
    
    const init = (id, settings) => {
        const root = document.getElementById(id);
        if (!root || root.dataset.initialised) {
            return;
        }
        root.dataset.initialised = '1';
    
        const status = root.querySelector('.qtype-audioresponse-status');
        const countdown = root.querySelector('.qtype-audioresponse-countdown');
        const timer = root.querySelector('.qtype-audioresponse-timer');
    
        let stream = null;
        let recorder = null;
        let chunks = [];
        let stopped = false;
        let startAt = 0;
        let uploadStarted = false;
        const diagnostic = (message, data) => {
            if (window.console && console.log) {
                console.log('Audio Response:', message, data || '');
            }
        };
    
        const setStatus = (text) => {
            status.textContent = text;
        };
    
        const wait = (ms) => new Promise(resolve => setTimeout(resolve, ms));
    
        const countdownSeconds = async (seconds) => {
            for (let remaining = seconds; remaining > 0; remaining--) {
                countdown.textContent = remaining;
                await wait(1000);
            }
            countdown.textContent = '';
        };
    
        const stopTracks = () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            stream = null;
        };
    
        const fail = (message) => {
            setStatus(message);
            countdown.textContent = '';
            timer.textContent = '';
            stopTracks();
        };
    
        const uploadBlob = (blob, filename) => {
            uploadStarted = true;
            diagnostic('uploadBlob()', {size: blob.size, type: blob.type, filename: filename});
            setStatus(''); 
            const formData = new FormData();
            formData.append('repo_upload_file', blob, filename);
            formData.append('sesskey', M.cfg.sesskey);
            formData.append('repo_id', settings.uploadRepositoryId);
            formData.append('itemid', settings.draftItemId);
            formData.append('savepath', '/');
            formData.append('ctx_id', settings.contextId);
            formData.append('overwrite', '1');
    
            const request = new XMLHttpRequest();
            request.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    setStatus(M.util.get_string('recordingfinished', 'qtype_audioresponse') +
                        ' ' + Math.round(event.loaded / event.total * 100) + '%');
                }
            });
            request.addEventListener('error', () => {
                setStatus(M.util.get_string('uploadfailed', 'qtype_audioresponse'));
            });
            request.addEventListener('abort', () => {
                setStatus(M.util.get_string('uploadfailed', 'qtype_audioresponse'));
            });
            request.addEventListener('load', () => {
                if (request.status !== 200) {
                    setStatus(M.util.get_string('uploadfailed', 'qtype_audioresponse'));
                    return;
                }
                try {
                    const response = JSON.parse(request.responseText);
                    if (response.errorcode) {
                        throw new Error(response.errorcode);
                    }
                    setStatus(M.util.get_string('uploadcomplete', 'qtype_audioresponse'));
                } catch (e) {
                    setStatus(M.util.get_string('uploadfailed', 'qtype_audioresponse'));
                }
            });
            request.open('POST', M.cfg.wwwroot + '/repository/repository_ajax.php?action=upload');
            request.send(formData);
        };
    
        const finish = () => {
            if (stopped) {
                return;
            }
            stopped = true;
            timer.textContent = '';
            setStatus(M.util.get_string('recordingfinished', 'qtype_audioresponse'));
            diagnostic('finish()', {state: recorder ? recorder.state : 'no recorder'});
            if (recorder && recorder.state !== 'inactive') {
                diagnostic('calling recorder.stop()', {state: recorder.state});
                recorder.stop();
            } else {
                stopTracks();
            }
        };
    
        const beginRecording = async () => {
            if (!checkCanWork()) {
                fail(M.util.get_string('nowebrtc', 'qtype_audioresponse'));
                return;
            }
    
            try {
                stream = await navigator.mediaDevices.getUserMedia({audio: true});
            } catch (e) {
                fail(M.util.get_string('microphonepermission', 'qtype_audioresponse'));
                return;
            }
    
            chunks = [];
            stopped = false;
            diagnostic('getUserMedia success', {trackState: stream.getAudioTracks()[0] ? stream.getAudioTracks()[0].readyState : 'none', trackEnabled: stream.getAudioTracks()[0] ? stream.getAudioTracks()[0].enabled : null, trackMuted: stream.getAudioTracks()[0] ? stream.getAudioTracks()[0].muted : null});

            diagnostic('Before recorder.start()', {
                streamActive: stream.active,
                trackCount: stream.getTracks().length,
                tracks: stream.getTracks().map(function(track) {
                    return {
                        kind: track.kind,
                        readyState: track.readyState,
                        enabled: track.enabled,
                        muted: track.muted
                    };
                })
            });
    
            diagnostic('MediaRecorder constructor test', {
                supported: typeof MediaRecorder !== 'undefined',
                webm: MediaRecorder.isTypeSupported('audio/webm'),
                webmOpus: MediaRecorder.isTypeSupported('audio/webm;codecs=opus'),
                oggOpus: MediaRecorder.isTypeSupported('audio/ogg;codecs=opus')
            });

            try {
                recorder = new MediaRecorder(stream, {mimeType: 'audio/webm'});
                diagnostic('MediaRecorder created', {
                    mimeType: recorder.mimeType,
                    state: recorder.state
                });
            } catch (e) {
                diagnostic('MediaRecorder constructor failed', {
                    name: e && e.name ? e.name : 'unknown',
                    message: e && e.message ? e.message : String(e)
                });
                fail(M.util.get_string('recordingfailed', 'qtype_audioresponse'));
                return;
            }

            recorder.onstart = () => diagnostic('onstart', {state: recorder.state, mimeType: recorder.mimeType});

            recorder.ondataavailable = (event) => {
                diagnostic('ondataavailable', {size: event.data ? event.data.size : 0, type: event.data ? event.data.type : '', state: recorder.state});
                if (event.data && event.data.size) {
                    chunks.push(event.data);
                }
            };
    
            recorder.onerror = (event) => {
                diagnostic('onerror', {
                    errorName: event && event.error ? event.error.name : 'unknown',
                    errorMessage: event && event.error ? event.error.message : 'unknown',
                    state: recorder.state
                });
                stopTracks();
                fail(M.util.get_string('recordingfailed', 'qtype_audioresponse'));
            };

            recorder.onstop = () => {
                diagnostic('onstop', {state: recorder.state, chunks: chunks.length});
                const type = recorder.mimeType || 'audio/webm';
                const blob = new Blob(chunks, {type});
                diagnostic('final Blob created', {size: blob.size, type: blob.type, chunks: chunks.length});
                stopTracks();

                if (!blob.size) {
                    fail(M.util.get_string('recordingfailed', 'qtype_audioresponse'));
                    return;
                }
                const filename = type.indexOf('ogg') !== -1 ?
                    'audioresponse.ogg' : 'audioresponse.webm';
                uploadBlob(blob, filename);
            };
    
            // Let MediaRecorder produce one continuous WebM/Opus recording.
            // We deliberately do not use a timeslice here: the complete recording
            // is assembled by MediaRecorder and delivered in the final dataavailable
            // event when stop() is called. This matches the proven EASE implementation.
            try {
                recorder.start();

                diagnostic('recorder.start() returned', {
                    state: recorder.state,
                    mimeType: recorder.mimeType,
                    streamActive: stream.active,
                    trackState: stream.getAudioTracks()[0]
                        ? stream.getAudioTracks()[0].readyState
                        : 'no-audio-track'
                });

                window.setTimeout(function() {
                    diagnostic('Recorder state 100ms after start', {
                        state: recorder.state,
                        streamActive: stream.active,
                        trackState: stream.getAudioTracks()[0]
                            ? stream.getAudioTracks()[0].readyState
                            : 'no-audio-track'
                    });
                }, 100);
            } catch (e) {
                diagnostic('recorder.start() threw', {
                    name: e && e.name ? e.name : 'unknown',
                    message: e && e.message ? e.message : String(e)
                });
                fail(M.util.get_string('recordingfailed', 'qtype_audioresponse'));
                return;
            }
            startAt = Date.now();
            setStatus(M.util.get_string('recordingnow', 'qtype_audioresponse'));
    
            const duration = Number(settings.duration);
            const update = () => {
                if (stopped) {
                    return;
                }
                const elapsed = Math.floor((Date.now() - startAt) / 1000);
                const remaining = Math.max(0, duration - elapsed);
                const mins = Math.floor(remaining / 60);
                const secs = String(remaining % 60).padStart(2, '0');
                timer.textContent = String(mins).padStart(2, '0') + ':' + secs;
                if (remaining <= 0) {
                    finish();
                    return;
                }
                window.setTimeout(update, CHECK_INTERVAL);
            };
            update();
        };
    
        const run = async () => {
            setStatus(M.util.get_string('preparing', 'qtype_audioresponse'));
    
            const delay = Number(settings.delay);
            const preparation = Number(settings.preparation);
    
            if (delay > 0) {
                await wait(delay * 1000);
            }
    
            if (preparation > 0) {
                setStatus(M.util.get_string(
                    'recordingstarting', 'qtype_audioresponse', preparation));
                await countdownSeconds(preparation);
            }
    
            playStartSignal();
            await wait(1000);
            await beginRecording();
        };
    
        run();
    };
    

    return {
        init: init
    };
});
